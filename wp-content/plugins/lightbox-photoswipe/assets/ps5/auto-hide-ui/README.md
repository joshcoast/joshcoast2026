# Auto hide UI for PhotoSwipe

This plugin adds automatic hiding of the UI to [PhotoSwipe 5](https://github.com/dimsemenov/PhotoSwipe) by Dmytro Semenov in a similar way as it was done in PhotoSwipe 4:

When the user moved the mouse, a timeout will start after which the UI will be hidden when the mouse is not moved. When the mouse is moved again, the UI will be displayed again. If touch device is detected due to a "tap" event instead of mouse move, the UI will stay visible as it can be hidden or displayed on mobile devices by tapping the display once.

This plugin is also used in [Lightbox with PhotoSwipe](https://wordpress.org/plugins/lightbox-photoswipe/), see https://wordpress-demo.arnowelzel.de/lightbox-with-photoswipe-5/ as an example.

## Using the plugin

To use the plugin, import the module and create the plugin using the lightbox instance as parameter before you init the lightbox. The timeout for hiding the UI is 4000 ms (4 seconds) by default, but you can use a different time as well with the `idleTime` option.

```
<script type="module">
import PhotoSwipeLightbox from 'photoswipe/dist/photoswipe-lightbox.esm.min.js';
import PhotoSwipeAutoHideUI from 'photoswipe-auto-hide-ui/photoswipe-auto-hide-ui.esm.min.js';

const lightbox = new PhotoSwipeLightbox({
  gallerySelector: '#gallery',
  childSelector: '.pswp-gallery__item',
  pswpModule: () => import('photoswipe/dist/photoswipe.esm.js'),
});

const autoHideUI = new PhotoSwipeAutoHideUI(lightbox, {
  // Plugin options
  idleTime: 4000  // timeout to hide in ms (default is 4000 or 4 seconds)
});

// make sure you init photoswipe core after plugins are added
lightbox.init();
</script>
```

## Keep "dynamic caption" visible

If you also use the ["dynamic caption" plugin](https://github.com/dimsemenov/photoswipe-dynamic-caption-plugin) by Dmytro Semenov you may want to keep the caption visible when the UI is hidden. For this you can use the following CSS rule which also applies when you hide the UI on a mobile device by tapping the image:

```
.pswp__dynamic-caption--aside,
.pswp__dynamic-caption--below {
  opacity: 1 !important;
}
```

## Changelog

### 1.0.1

Fixed potential error when closing the lightbox.

### 1.0.0

Initial release.
