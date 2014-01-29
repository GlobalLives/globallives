module.exports = function(grunt) {
	'use strict';

	grunt.initConfig({

		jshint: {
			all: [
				'Gruntfile.js'
				// 'js/main.js',
				// 'js/video.js'
			]
		},
		uglify: {
			all: {
				options: {
					mangle: false
				},
				files: {
					'js/app.min.js': ['js/main.js','js/video.js'],
					'js/vendor.min.js': [
						'bower_components/bootstrap/js/bootstrap-button.js',
						'bower_components/bootstrap/js/bootstrap-carousel.js',
						'bower_components/bootstrap/js/bootstrap-dropdown.js',
						'bower_components/bootstrap/js/bootstrap-modal.js',
						'bower_components/bootstrap/js/bootstrap-popover.js',
						'bower_components/bootstrap/js/bootstrap-tab.js',
						'bower_components/bootstrap/js/bootstrap-tooltip.js',
						'bower_components/bootstrap/js/bootstrap-transition.js',
						'bower_components/jquery-ui/ui/jquery.ui.core.js',
						'bower_components/jquery-ui/ui/jquery.ui.widget.js',
						'bower_components/jquery-ui/ui/jquery.ui.mouse.js',
						'bower_components/jquery-ui/ui/jquery.ui.slider.js',
						'bower_components/jquery-ui-touch-punch/jquery.ui.touch-punch.js',
						'bower_components/jquery-cycle/jquery.cycle.lite.js'
					],
					'js/d3.min.js': ['bower_components/d3/d3.js']
				}
			}
		},
		less: {
			all: {
				options: {
					cleancss: true
				},
				files: {
					'css/style.min.css': 'less/style.less'
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
				files: '<%= jshint.all %>',
				tasks: ['jshint','uglify']
			},
			styles: {
				files: 'less/*.less',
				tasks: ['less']
			}
		}

	});

	grunt.loadNpmTasks('grunt-contrib-jshint');
	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-contrib-less');
	grunt.loadNpmTasks('grunt-contrib-watch');
	grunt.loadNpmTasks('grunt-mocha');

	grunt.registerTask('default',['jshint','uglify','less']);
	grunt.registerTask('test','mocha');
};