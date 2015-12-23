module.exports = (grunt) ->
  grunt.initConfig
    pkg: grunt.file.readJSON 'package.json'
    appConfig:
      src: 'bower_components'
      dest: 'build'
    clean:
      options:
        force: yes
      build:
        src: [
          '<%= appConfig.dest %>/'

        ]
    copy:
      dist:
        files: [
          # animate.css
          {
            src: ['<%= appConfig.src %>/animate.css/animate.min.css']
            dest: '<%= appConfig.dest %>/css/animate.min.css'
          },

          # lazysizes
          {
            src: ['<%= appConfig.src %>/lazysizes/lazysizes.min.js']
            dest: '<%= appConfig.dest %>/js/lazysizes.min.js'
          },

          # owl.carousel (2.x)
          {
            src: ['<%= appConfig.src %>/owl.carousel/dist/owl.carousel.min.js']
            dest: '<%= appConfig.dest %>/js/owl.carousel.min.js'
          },
          {
            src: ['<%= appConfig.src %>/owl.carousel/dist/assets/owl.carousel.min.css']
            dest: '<%= appConfig.dest %>/css/owl.carousel.min.css'
          },
          {
            src: ['<%= appConfig.src %>/owl.carousel/dist/assets/owl.theme.default.min.css']
            dest: '<%= appConfig.dest %>/css/owl.theme.default.min.css'
          },

          # owlcarousel (1.x)
          {
            src: ['<%= appConfig.src %>/owlcarousel/owl-carousel/owl.carousel.min.js']
            dest: '<%= appConfig.dest %>/js/owl.carousel-v1.min.js'
          },
          {
            src: ['<%= appConfig.src %>/owlcarousel/owl-carousel/owl.carousel.css']
            dest: '<%= appConfig.dest %>/css/owl.carousel-v1.css'
          },
          {
            src: ['<%= appConfig.src %>/owlcarousel/owl-carousel/owl.theme.css']
            dest: '<%= appConfig.dest %>/css/owl.theme-v1.css'
          },
          {
            src: ['<%= appConfig.src %>/owlcarousel/owl-carousel/owl.transitions.css']
            dest: '<%= appConfig.dest %>/css/owl.transition-v1.css'
          },


        ],


  grunt.loadNpmTasks 'grunt-contrib-clean'
  grunt.loadNpmTasks 'grunt-contrib-copy'

  # Register tasks
  grunt.registerTask 'default', ->
    taskList = [
      'clean'
      'copy'
    ]
    if grunt.option('watch')
      taskList.push 'watch'
    grunt.task.run taskList