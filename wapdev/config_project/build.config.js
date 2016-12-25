module.exports = {

    version:'20151014',
    filename: 'agg',
    apiShop:'config_project/api.config.shop.js',
    apiTest:'config_project/api.config.test.js',
    apiDev:'config_project/api.config.dev.js',
    apiOnline:'config_project/api.config.online.js',
    localApiFiles:'js/config.js',
    compressFiles: {
        'scripts': [
            '**/*.js',
            '!**/*.min.js',
            '!js/config.js',
            '!gulpfile.js',
            '!config_project/**/*',
            '!node_modules/**/*'
        ],
        'styles': [
            '**/*.css',
            '!**/*.min.css',
            '!config_project/**/*',
            '!node_modules/**/*'
        ],
        'htmls':[
            '**/*.html',
            '**/*.mp3',
            '!config_project/**/*',
            '!node_modules/**/*'
        ],
        'images':[
            '**/*.png',
            '**/*.jpg',
            '**/*.gif',
            '!config_project/**/*',
            '!node_modules/**/*'
        ]
    },
    unCompressFiles:{
        'scripts': [
            '**/*.min.js',
            '!js/config.js',
            '!gulpfile.js',
            '!config_project/**/*',
            '!node_modules/**/*'
        ],
        'styles': [
            '**/*.min.css',
            '!config_project/**/*',
            '!node_modules/**/*'
        ],
        'htmls':[
        ],
        'images':[
        ]
    },
    allFiles:{
        'scripts': [
            '**/*.js',
            '!js/config.js',
            '!gulpfile.js',
            '!config_project/**/*',
            '!node_modules/**/*'
        ],
        'styles': [
            '**/*.css',
            '!config_project/**/*',
            '!node_modules/**/*'
        ],
        'htmls':[
            '**/*.html',
            '!config_project/**/*',
            '!node_modules/**/*'
        ],
        'images':[
            '**/*.png',
            '**/*.jpg',
            '**/*.gif',
            '!config_project/**/*',
            '!node_modules/**/*'
        ]
    },
    output:{
        'wapbuild':{
            'url':'../wapbuild',
            'js':'../wapbuild/js',
            'htmlFiles':'../wapbuild/**/*.html'
        },
        'wapbuildtest':{
            'url':'../wap',
            'js':'../wap/js',
            'htmlFiles':'../wap/**/*.html'
        }
    }
};