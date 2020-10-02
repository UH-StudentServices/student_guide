Helsingin Yliopisto Student Guide Theme (uhsg_theme)
===========================================

Requirements:
  - Nodejs (npm)

About:
  Fonts and scss assets come from the Helsinki University styleguide.
  these are installed with yarn and gulp.

Getting started (for development):
  - NOTE: These commands have to be run from the repository root.
  - Install yarn packages
      $ yarn install
  - Install HY Styleguide
      $ yarn run gulp styleguide-update
  - Optional: Lint Javascript
      $ yarn run gulp lint
  - Optional: Initialize SASS watcher
      $ yarn run gulp watch
  - Optional: Automatically reload changes in your browser
      $ yarn run gulp browsersync
