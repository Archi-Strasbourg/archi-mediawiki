/*jslint node: true*/
module.exports = function (grunt) {
    'use strict';

    grunt.loadNpmTasks('grunt-jslint');
    grunt.loadNpmTasks('grunt-phpcs');
    grunt.loadNpmTasks('grunt-shipit');

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
                servers: 'pierre@dev.rudloff.pro'
            }
        }
    });

    grunt.registerTask('lint', ['jslint', 'phpcs']);
    grunt.registerTask('pull', function () {
        grunt.shipit.remote('cd /var/www/archi-mediawiki/; git pull; composer install --no-dev; composer updatedb -- --quick; cd redirect/; composer install --no-dev', this.async());
    });
    grunt.registerTask('staging', ['shipit:staging', 'pull']);
};
