# Download for PhotoSwipe

This plugin adds a download button to the UI to [PhotoSwipe 5](https://github.com/dimsemenov/PhotoSwipe) by Dmytro Semenov.

This plugin is also used in [Lightbox with PhotoSwipe](https://wordpress.org/plugins/lightbox-photoswipe/), see https://wordpress-demo.arnowelzel.de/lightbox-with-photoswipe-5/ as an example.

## Using the plugin

To use the plugin, import the module and create the plugin using the lightbox instance as parameter before you init the lightbox.

```
<script type="module">
import PhotoSwipeLightbox from 'photoswipe/dist/photoswipe-lightbox.esm.min.js';
import PhotoSwipeDownload from 'photoswipe-download/photoswipe-download.esm.min.js';

const lightbox = new PhotoSwipeLightbox({
  gallerySelector: '#gallery',
  childSelector: '.pswp-gallery__item',
  pswpModule: () => import('photoswipe/dist/photoswipe.esm.js'),
});

const downloadPlugin = new PhotoSwipeDownload(lightbox);

// make sure you init photoswipe core after plugins are added
lightbox.init();
</script>
```

You can also translate the tooltip label of the download icon:

```
const downloadPlugin = new PhotoSwipeDownload(lightbox, {
  downloadTitle: 'Download image'
});
```

## Changelog

### 1.0.0

Initial release
