/*jslint node: true*/
module.exports = function (grunt) {
    'use strict';

    grunt.loadNpmTasks('grunt-jslint');
    grunt.loadNpmTasks('grunt-phpcs');
    grunt.loadNpmTasks('grunt-shipit');
    grunt.loadNpmTasks('shipit-git-update');
    grunt.loadNpmTasks('shipit-composer-simple');
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
        shipit: {
            options: {
                branch: 'develop',
                servers: 'pierre@archi-strasbourg.org',
                composer: {
                    noDev: true,
                    cmd: 'updatedb -- --quick'
                }
            },
            staging: {
                deployTo: '/home/vhosts/fabien/archi-mediawiki/'
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
    grunt.registerTask('staging', ['shipit:staging', 'update', 'composer:install', 'composer:cmd']);
};
