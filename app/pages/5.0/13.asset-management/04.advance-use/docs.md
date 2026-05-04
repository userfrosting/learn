---
title: Enabling Sass, Less, Vue and React
metadata:
    description: 
taxonomy:
    category: docs
---

By using Webpack Encore to manage frontend assets, Sass, Less, Vue and React can be used with UserFrosting out of the box.

## Sass/SCSS

[notice=note]Sass is enabled by default in UserFrosting 5, as it's required by the *AdminLTE Theme*.[/notice]

To enable [Sass/SCSS](https://sass-lang.com) support, first enable Sass loader inside `webpack.config.js`, install the required npm dependencies, and restart Webpack.

1. Edit `webpack.config.js`
    
    **webpack.config.js**
    ```js
    Encore
        // ...

        // enables Sass/SCSS support
        .enableSassLoader()

        // ...
    ;
    ```

2. Install npm dependencies
    ```bash
    npm install sass-loader@^13.0.0 sass --save-dev
    ```

3. Restart Encore
    ```bash
    npm run dev
    ```

Sass can now be imported in your entry files:

**app/assets/main.js**
```js
- import './styles/app.css';
+ import './styles/app.scss';
```

For more information, check out [Encore Documentation](https://symfony.com/doc/current/frontend/encore/simple-example.html#using-sass-less-stylus)

## Less

To enable [Less](https://lesscss.org) support, first enable Less loader inside `webpack.config.js`, install the required npm dependencies, and restart Webpack.

1. Edit `webpack.config.js`
    
    **webpack.config.js**
    ```js
    Encore
        // ...

        // enables Less support
        .enableLessLoader()

        // ...
    ;
    ```

2. Install npm dependencies
    ```bash
    npm install less-loader@^11.0.0 --save-dev
    ```

3. Restart Encore
    ```bash
    npm run dev
    ```

For more information, check out [Encore Documentation](https://symfony.com/doc/current/frontend/encore/css-preprocessors.html)

## Vue.js

To enable [Vue.js](http://vuejs.org) support, first enable Vue Loader inside `webpack.config.js`:


**webpack.config.js**
```js
Encore
    // ...

    // enables Vue Loader
    .enableVueLoader()

    // ...
;
```

Then restart Encore. When you do, it will give you a command you can run to install any missing dependencies. After running that command and restarting Encore, you're done!

Any `.vue` files that you require will be processed correctly. You can also configure the `vue-loader` options by passing an options callback to `enableVueLoader()`. 

For more information, check out [Encore Documentation](https://symfony.com/doc/current/frontend/encore/vuejs.html). 

[notice]Future version of UserFrosting will make use of Vue.JS. You can checkout a proof of concept of a Vue based interface running inside UserFrosting [on Github](https://github.com/userfrosting/demo-vue/tree/main)[/notice]

## React

Using React? First add some dependencies with npm:

```bash
npm install react react-dom prop-types --save
```

Enable react in your `webpack.config.js`:

```js
  Encore
      // ...
     .enableReactPreset()
  ;
```

Then restart Encore. When you do, it will give you a command you can run to install any missing dependencies. After running that command and restarting Encore, you're done!

Your .js and .jsx files will now be transformed through babel-preset-react.

For more information, check out [Encore Documentation](https://symfony.com/doc/current/frontend/encore/reactjs.html)
