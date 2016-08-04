/*jslint node: true*/
module.exports = function (grunt) {
    'use strict';

    grunt.loadNpmTasks('grunt-jslint');
    grunt.loadNpmTasks('grunt-phpcs');
    grunt.loadNpmTasks('grunt-shipit');
    grunt.loadNpmTasks('shipit-git-update');
    grunt.loadNpmTasks('shipit-composer-simple');

    grunt.initConfig({
        jslint: {
            Gruntfile: {
                src: 'Gruntfile.js'
            }
        },
        phpcs: {
            options: {
                standard: 'PSR2',
                bin: 'redirect/vendor/bin/phpcs'
            },
            redirect: {
                src: ['redirect/*.php']
            }
        },
        shipit: {
            options: {
                branch: 'develop',
                servers: 'pierre@dev.rudloff.pro',
                composer: {
                    noDev: true,
                    cmd: 'updatedb'
                }
            },
            staging: {
                deployTo: '/var/www/archi-mediawiki/'
            },
            'staging:redirect': {
                deployTo: '/var/www/archi-mediawiki/redirect'
            }
        }
    });

    grunt.registerTask('lint', ['jslint', 'phpcs']);
    grunt.registerTask('staging', ['shipit:staging', 'update', 'composer:install', 'composer:cmd', 'shipit:staging:redirect', 'composer:install']);
};
