import $ from 'jquery';

class Search {
    // 1. Describe or create/initiate our object.
    constructor() {
        this.addSearchHTML();
        this.resultsDiv = $("#search-overlay__results");
        this.openButton = $(".js-search-trigger");
        this.closeButton = $(".search-overlay__close");
        this.searchOverlay = $(".search-overlay");
        this.isSpinnerShowing = false;
        this.searchField = $("#search-term");
        this.events(); // call and load the events.
        this.previousValue;
        this.typingTimer;
    }

    // 2. Events
    events() {
		this.openButton.on('click', this.openOverlay.bind(this));
        // the "on" method in jQuery sets the "this" to the element. We use the "bind()"
        // method to tell jQuery to not do that and set it to our object with "this".
        this.closeButton.on("click", this.closeOverlay.bind(this));
        this.searchField.on("keyup", this.typingLogic.bind(this)); // Keydown is too fast.
    }

    // 3. Methods (function, actions...)
    typingLogic() {
        // Only if the key strokes have changed the value do we want to do any of this.
        if (this.searchField.val() != this.previousValue) {
            clearTimeout(this.typingTimer); // clear the timer so we don't hit the search repeatedly.

            if (this.searchField.val()) {
                if ( !this.isSpinnerShowing ) {
                    this.resultsDiv.html('<div class="brent-spiner-loader"><i class="fa-solid fa-fan brent-spiner"></i></div>');
                    this.isSpinnerShowing = true;
                }
                this.typingTimer = setTimeout(this.getResults.bind(this), 750);
            } else {
                this.resultsDiv.html('');
                this.isSpinnerShowing = false;
            }
        }
        this.previousValue = this.searchField.val();
    }

    getResults() {

        // We now have a custom api that returns all the post types.
        $.getJSON(joshcoastData.root_url + '/wp-json/joshcoast/v1/search?term=' + this.searchField.val(), (searchResults) => {
            this.resultsDiv.html(`
            <div class="row">
                <div class="one-third">
                    <h2>General Information</h2>
                    ${searchResults.generalInfos.length ? '<ul>' : '<p>No General Information matches that search.</p>'}
                    ${searchResults.generalInfos.map(item => `<li><a href='${item.permalink}'>${item.title}</a></li>`).join('')}
                    ${searchResults.generalInfos.length ? '</ul>' : '' }
                </div>
                <div class="one-third">
                    <h2>Clients!</h2>
                    ${searchResults.clients.length ? '<ul>' : `<p>No clients match that search. <a href="${ joshcoastData.root_url }/portfolio">View Full Portfolio</a></p>`}
                    ${searchResults.clients.map(item => `
                        <li>
                            <a href='${item.permalink}'>${item.title}</a>
                            <img src='${item.image}' />
                            <p>${item.excerpt}</p>
                        </li>
                        `).join('')}
                    ${searchResults.clients.length ? '</ul>' : '' }
                </div>
                <div class="one-third">
                    <h2>Blog</h2>
                    ${searchResults.blogs.length ? '<ul>' : `<p>No Notes match that search. <a href="${ joshcoastData.root_url }/notes">View all Notes</a></p>`}
                    ${searchResults.blogs.map(item => `<li><a href='${item.permalink}'>${item.title}</a> ${item.post_type == 'post' ? `by ${item.authorName}` : ''} </li>`).join('')}
                    ${searchResults.blogs.length ? '</ul>' : '' }
                </div>
            </div>
            `);
            this.isSpinnerShowing = false;
        });

    }
    openOverlay() {
        this.searchOverlay.addClass("search-overlay--active");
        $('body').addClass("no-scroll");
        this.searchField.val('');
        setTimeout(() => this.searchField.focus(), 301 );
        this.isSpinnerShowing = true;
    }

    closeOverlay() {
        this.searchOverlay.removeClass("search-overlay--active");
        $('body').removeClass("no-scroll");
    }

    addSearchHTML() {
        $("body").append(`
            <div class="search-overlay">
                <div class="search-overlay__top">
                    <i class="fa-solid fa-magnifying-glass search-overlay__icon" aria-hidden="true" ></i>
                    <div class="container">
                        <input type="text" class="search-term" placeholder="What are you looking for?" id="search-term">
                    </div>
                    <i class="fa-regular fa-circle-xmark search-overlay__close" aria-hidden="true"></i>
                </div>
                <div class="container">
                    <div id="search-overlay__results" class="search-overlay__results">
                    </div>
                </div>
            </div>
        `);
    }
}

export default Search;