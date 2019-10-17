/*jslint node: true*/
module.exports = function (grunt) {
    'use strict';

    grunt.loadNpmTasks('grunt-jslint');
    grunt.loadNpmTasks('grunt-phpcs');
    grunt.loadNpmTasks('grunt-jsonlint');
    grunt.loadNpmTasks('grunt-fixpack');

    grunt.initConfig({
        jslint: {
            Gruntfile: {
                src: 'Gruntfile.js'
            }
        },
        phpcs: {
            options: {
                standard: 'PSR2',
                // We can't install it locally with Composer because it conflicts with MediaWiki
                bin: '/usr/bin/phpcs'
            },
            settings: {
                src: ['LocalSettings.php', 'dbconfig.php', 'namespaces.php']
            }
        },
        jsonlint: {
            manifests: {
                src: '*.json',
                options: {
                    format: true
                }
            },
            redirect: {
                src: 'redirect/*.json',
                options: {
                    format: true
                }
            }
        },
        fixpack: {
            package: {
                src: 'package.json'
            }
        }
    });

    grunt.registerTask('lint', ['jslint', 'fixpack', 'jsonlint', 'phpcs']);
};
