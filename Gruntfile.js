/*jslint node: true*/
module.exports = function (grunt) {
    'use strict';

    grunt.loadNpmTasks('grunt-jslint');
    grunt.loadNpmTasks('grunt-phpcs');
    grunt.loadNpmTasks('grunt-shipit');
    grunt.loadNpmTasks('shipit-git-update');

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
            staging: {
                branch: 'develop',
                servers: 'pierre@dev.rudloff.pro',
                deployTo: '/var/www/archi-mediawiki/',
                postUpdateCmd: 'composer install --no-dev; composer updatedb -- --quick; cd redirect/; composer install --no-dev'
            }
        }
    });

    grunt.registerTask('lint', ['jslint', 'phpcs']);
    grunt.registerTask('staging', ['shipit:staging', 'update']);
};
