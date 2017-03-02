Helsingin Yliopisto Student Guide Theme (uhsg_theme)
===========================================

Requirements:
  - Nodejs (npm)

About:
  Fonts and scss assets come from the Helsinki University styleguide.
  these are installed with bower and gulp.

Getting started (for development):
  - NOTE: from now on these commands have to be run from the repository root.
  - Install npm packages (to install bower and gulp)
      $ npm install
  - Install HY Styleguide
      $ gulp styleguide-update
  - Lint Javascript
      $ gulp lint
  - Optional: Initialize SASS watcher
      $ gulp watch
  - Optional: Automatically reload changes in your browser
      $ gulp browsersync
