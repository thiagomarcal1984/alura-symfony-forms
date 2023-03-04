const Encore = require('@symfony/webpack-encore');

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    // directory where compiled assets will be stored
    .setOutputPath('public/build/')
    // public path used by the web server to access the output path
    .setPublicPath('/build')
    // Se habilitar o RuntimeChunk, cria o arquivo runtime.js.
    .disableSingleRuntimeChunk() 
    /*
     * FEATURE CONFIG
     *
     * Enable & configure other features below. For a full
     * list of features, see:
     * https://symfony.com/doc/current/frontend.html#adding-more-features
     */
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    // enables Sass/SCSS support
    .enableSassLoader() // Esta linha estava comentada na geração do arquivo.

    // uncomment if you use TypeScript
    //.enableTypeScriptLoader()
    
    // Gera style.css a partir da compilação de app.scss
    .addStyleEntry('style', './assets/styles/app.scss') 
;

module.exports = Encore.getWebpackConfig();
