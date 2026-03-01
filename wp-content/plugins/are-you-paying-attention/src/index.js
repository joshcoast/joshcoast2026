import "./index.scss"
// The WP Scripts we are using has made a way to recognise this import at a global level
// and automatically grab the right component. So, no need to install this component with npm.
// BUT we need to tell WordPress to load the components js file in the global scope.
// SO check out plugins/are-you-paying-attention/index.php and the wp_register_script for 'wp-element'.
import { TextControl, Flex, FlexBlock, FlexItem, Button, Icon, PanelBody, PanelRow, ColorPicker } from "@wordpress/components"
import { InspectorControls, BlockControls, AlignmentToolbar, useBlockProps } from "@wordpress/block-editor"
import {SketchPicker} from "react-color"

// these wierd () just make it so the function gets called without a name.
// The variables in this function are scoped to only this function.
(function() {
    let locked = false

    wp.data.subscribe(function() {
        const results = wp.data.select("core/block-editor").getBlocks().filter(function(block) {
            return block.name == "ourplugin/are-you-paying-attention" && block.attributes.correctAnswer == undefined
        })

        if (results.length && locked == false) {
            locked = true
            // Gray out the save/update button.
            wp.data.dispatch("core/editor").lockPostSaving("noanswer")
        }

        if (!results.length && locked) {
            locked = false
            // Allow the save/update button.
            wp.data.dispatch("core/editor").unlockPostSaving("noanswer")
        }
    })
})()

wp.blocks.registerBlockType("ourplugin/are-you-paying-attention", {
    title: "Are You Paying Attention?",
    icon: "smiley",
    category: "common",
    attributes: {
        question: { type: "string" },
        answers: { type: "array", default: [""] },
        correctAnswer: {type: "number", default: undefined},
		bgColor: { type: "string", default: "#EBEBEB"},
		textColor: { type: "string", default: "#1a1a1a"},
		theAlignment: { type: "string", default: "left"}
    },
	example: {
		attributes: {
			question: "What is my nbame?",
			correctAnswer: 3,
			answers: ['Meowsalot', 'Barksalot', 'Purrsloud', 'Josh'],
			theAlignment: "center",
			bgColor: "#CFE8F1"
		}
	},
    // The admin edit screen.
    edit: EditComponent,

    // Don't save to the database, instead we will pass the props to PHP in index.php.
    // We are doing this so when we edit the "front end" we won't need to re-save every post.
    save: function (props) {
        return null
    }
})

// You can name this edit function anything, but in react you do want to use an
// Uppercase at the start for components. This is what shows in the edit screen.
function EditComponent (props) {
	// Tell wp to manage the block props (clicking on a block in the admin etc.)
	// Make sure to give "blockProps" (can be named whatever btw) to the wrapper element
	const blockProps = useBlockProps({
		className: "paying-attention-edit-block",
		style: { backgroundColor: props.attributes.bgColor, color: props.attributes.textColor }
	})

    function updateQuestion(value) {
        props.setAttributes({question: value})
    }

    function deleteAnswer(indexToDelete) {
        // When we modify an array we don't want to mutate a property directly,
        // so we make a copy of it first.
        // The filter function: Will call the given function once for each item in the array.
        // If in the body of the filter function we return true, that item will be included.
        const newAnswers = props.attributes.answers.filter(function(x, index) {
            // we want to compare the index we want to delete to the one that's in our filter.
            return index != indexToDelete
        })
        props.setAttributes({answers: newAnswers})

        // If this answer was marked as the correct answer, we need to set correct
        // answer back to undefined.
        if (indexToDelete == props.attributes.correctAnswer) {
            props.setAttributes({correctAnswer: undefined})
        }
    }

    function markAsCorrect(index) {
        props.setAttributes({correctAnswer: index})
    }

    // "<TextControl /> is a component made by WP.
    return (
		// blockProps is using the spread syntax. What ever properties live inside blockProps (see above) each one will be applied to this wrapper element.
        <div {...blockProps} >
			<BlockControls>
				<AlignmentToolbar value={props.attributes.theAlignment} onChange={x => props.setAttributes({theAlignment: x})}/>
			</BlockControls>
			<InspectorControls>
				<PanelBody title="Background Color" initialOpen={false}>
					<SketchPicker
						color={props.attributes.bgColor}
						onChangeComplete={bgHex => props.setAttributes({bgColor: bgHex.hex})}
						disableAlpha={true}
					/>
				</PanelBody>
				<PanelBody title="Text Color" initialOpen={false}>
					<SketchPicker
						color={props.attributes.textColor}
						onChangeComplete={textHex => props.setAttributes({textColor: textHex.hex})}
						disableAlpha={true}
					/>
				</PanelBody>
			</InspectorControls>
            <TextControl label='Question:' value={props.attributes.question} onChange={updateQuestion} sytle={{fontSize: "20px"}} />
            <p style={{fontSize: "13px", margin: "20px 0 8px 0"}}>Answers:</p>
            {props.attributes.answers.map(function(answer, index){
                return(
                    <Flex>
                        <FlexBlock>
                            <TextControl value={answer} onChange={newValue => {
                                const newAnswers = props.attributes.answers.concat([])
                                newAnswers[index] = newValue
                                props.setAttributes({answers: newAnswers})
                            }} />
                        </FlexBlock>
                        <FlexItem>
                            <Button onClick={() => markAsCorrect(index)}>
                                <Icon className="mark-as-correct" icon={props.attributes.correctAnswer === index ? "star-filled" : "star-empty"} />
                            </Button>
                        </FlexItem>
                        <FlexItem>
                            <Button isLink className="attention-delete" onClick={() => deleteAnswer(index) }>Delete</Button>
                        </FlexItem>
                    </Flex>
                )
            })}
            <Button isPrimary onClick={() => {
                props.setAttributes({answers: props.attributes.answers.concat([""])})
            }}>Add another answer</Button>
        </div>
    )
}

/*
This is the standard way. However, if you change any of the HTML and add the deprecated version, you'd have to go and re-save every post that used this block.
    save: function (props) {
        return (
            <>
                <h1>This is the front end, and it's hard coded.</h1>
                <h2>Today the sky is completely and totally {props.attributes.skyColor} and the grass is {props.attributes.grassColor}.</h2>
            </>
        )
    },
    deprecated: [{
        attributes: {
            skyColor: { type: "string" },
            grassColor: { type: "string" }
        },
        save: function (props) {
            return (
                <>
                    <h1>This is the front end and it's hard coded.</h1>
                    <h3>Today the sky is completely {props.attributes.skyColor} and the grass is {props.attributes.grassColor}.</h3>
                </>
            )
        },
    },
        {
        attributes: {
            skyColor: { type: "string" },
            grassColor: { type: "string" }
        },
        save: function (props) {
            return (
                <>
                    <h1>This is the front end and it's hard coded.</h1>
                    <p>Today the sky is {props.attributes.skyColor} and the grass is {props.attributes.grassColor}.</p>
                </>
            )
        }
    }]
})*/
