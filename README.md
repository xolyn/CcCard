# CcCard

## Usage
Clone to local and move the folder `CcCard` to your plugin folder.

If you are using docker typecho and can't see plugin name and description, etc in the plugin managment page, make sure plugin folder and all files within have all privilegdes by running:

```bash
chmod -R \path\to\plugin\folder 777
```

Enable it and you can customize config:

## Customization
### Plugin configs
- Show title: the title of your posts
- Show author: you can set it to be the default author of your site or type it in the input box, which, if being left empty, fallbacks to the default author
- Show permalink: the permalink of your post, make sure you set the right website url in typecho setting.

### Component CSS

You can customize the style of the card in `CcCard/assets/style.css`

## Exclusion 
If you don't want Cc Card to be displayed on the page, simply add `<!--noCcCard-->` comment on the page specifically.
