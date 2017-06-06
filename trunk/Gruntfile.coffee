module.exports = (grunt) ->
  grunt.initConfig
    pkg: grunt.file.readJSON 'package.json'
    appConfig:
      src: 'bower_components'
      dest: 'build',
      public: 'public',
    clean:
      options:
        force: yes
      build:
        src: [
          '<%= appConfig.dest %>/'
        ],
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
          {
            src: ['<%= appConfig.src %>/owlcarousel/owl-carousel/grabbing.png']
            dest: '<%= appConfig.dest %>/css/grabbing.png'
          },

          # swiper
          {
            src: ['<%= appConfig.src %>/swiper/dist/js/swiper.jquery.min.js']
            dest: '<%= appConfig.dest %>/js/swiper.jquery.min.js'
          },
          {
            src: ['<%= appConfig.src %>/swiper/dist/css/swiper.min.css']
            dest: '<%= appConfig.dest %>/css/swiper.min.css'
          },


          {
            src: ['<%= appConfig.public %>/js/post-gallery-public.js']
            dest: '<%= appConfig.dest %>/js/post-gallery-public.js'
          },
          {
            src: ['<%= appConfig.public %>/js/litebox-gallery.class.js']
            dest: '<%= appConfig.dest %>/js/litebox-gallery.class.js'
          },
          {
            src: ['<%= appConfig.public %>/js/owl.postgallery.js']
            dest: '<%= appConfig.dest %>/js/owl.postgallery.js'
          },
          {
            src: ['<%= appConfig.public %>/js/swiper.postgallery.js']
            dest: '<%= appConfig.dest %>/js/swiper.postgallery.js'
          },

        ],

    uglify:
      dist:
        files:
            '<%= appConfig.dest %>/js/postgallery.min.js': [
              '<%= appConfig.dest %>/js/post-gallery-public.js',
              '<%= appConfig.dest %>/js/litebox-gallery.class.js'
              '<%= appConfig.dest %>/js/owl.postgallery.js'
              '<%= appConfig.dest %>/js/swiper.postgallery.js'
            ]

  grunt.loadNpmTasks 'grunt-contrib-clean'
  grunt.loadNpmTasks 'grunt-contrib-copy'
  grunt.loadNpmTasks 'grunt-contrib-uglify'

  # Register tasks
  grunt.registerTask 'default', ->
    taskList = [
      'clean'
      'copy'
      'uglify'
    ]
    if grunt.option('watch')
      taskList.push 'watch'
    grunt.task.run taskList