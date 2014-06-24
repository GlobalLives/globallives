module.exports = function(grunt) {
	'use strict';

	grunt.initConfig({

		jshint: {
			all: [
				'Gruntfile.js',
				'src/scripts/main.js',
				'src/scripts/video.js'
			]
		},
		uglify: {
			all: {
				options: {
					mangle: true,
					beautify: false
				},
				files: {
					'dist/jquery.js': ['bower_components/jquery/jquery.js'],
					'dist/main.js': [
					// D3
						'bower_components/d3/d3.js',
					// Bootstrap
						'bower_components/bootstrap/js/bootstrap-transition.js',
						// 'bower_components/bootstrap/js/bootstrap-alert.js',
						'bower_components/bootstrap/js/bootstrap-modal.js',
						// 'bower_components/bootstrap/js/bootstrap-dropdown.js',
						// 'bower_components/bootstrap/js/bootstrap-scrollspy.js',
						'bower_components/bootstrap/js/bootstrap-tab.js',
						'bower_components/bootstrap/js/bootstrap-tooltip.js',
						'bower_components/bootstrap/js/bootstrap-popover.js',
						'bower_components/bootstrap/js/bootstrap-button.js',
						// 'bower_components/bootstrap/js/bootstrap-collapse.js',
						'bower_components/bootstrap/js/bootstrap-carousel.js',
						// 'bower_components/bootstrap/js/bootstrap-typeahead.js',
						// 'bower_components/bootstrap/js/bootstrap-affix.js',
					// jQuery Plug-ins
						'bower_components/jquery-ui/ui/jquery.ui.core.js',
						'bower_components/jquery-ui/ui/jquery.ui.widget.js',
						'bower_components/jquery-ui/ui/jquery.ui.mouse.js',
						'bower_components/jquery-ui/ui/jquery.ui.slider.js',
						'bower_components/jquery-ui-touch-punch/jquery.ui.touch-punch.js',
						'bower_components/jquery-cycle/jquery.cycle.lite.js',
						'bower_components/jquery-geocomplete/jquery.geocomplete.js',
					// Theme JS
						'src/scripts/main.js',
						'src/scripts/video.js'
					]
				}
			}
		},
		less: {
			all: {
				options: {
					cleancss: true
				},
				files: {
					'src/styles/main.css': 'src/styles/main.less'
				}
			}
		},
		mocha: {
			test: {
				src: 'test/*.html',
				options: {
					reporter: 'Spec',
					run: true
				}
			}
		},
		watch: {
			scripts: {
				files: 'src/scripts/*.js',
				tasks: ['jshint','uglify']
			},
			styles: {
				files: 'src/styles/*.less',
				tasks: ['less','autoprefixer']
			}
		},
		autoprefixer: {
            dist: {
                files: {
                    'dist/main.css': 'src/styles/main.css'
                }
            }
        },
	});

	grunt.loadNpmTasks('grunt-contrib-jshint');
	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-contrib-less');
	grunt.loadNpmTasks('grunt-contrib-watch');
	grunt.loadNpmTasks('grunt-mocha');
	grunt.loadNpmTasks('grunt-autoprefixer');

	grunt.registerTask('default',['jshint','uglify','less','autoprefixer']);
	grunt.registerTask('test','mocha');
};