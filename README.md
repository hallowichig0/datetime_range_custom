CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Requirements
 * Installation
 * Maintainers


INTRODUCTION
------------

DateTime Range Custom form element using the jquery ui datetimepicker. Support Date & DateTime only.


REQUIREMENTS
------------

The Menu For All just requires the Drupal DateTime & DateTime Range project core:

 * DateTime (https://www.drupal.org/docs/8/core/modules/datetime)
 * DateTime Range (https://www.drupal.org/docs/8/core/modules/datetime-range)


INSTALLATION
------------

1. Edit your composer.json in your project directory and paste this code in the group of repositories.
```{
      "type": "vcs",
      "url": "git@github.com:hallowichig0/datetime_range_custom.git"
```

2. In your composer.json, search the installer-paths and add this line of codes to the group of installer-paths
```"web/modules/custom/{$name}/": ["type:drupal-custom-module"]```

3. Install using the composer:
```composer require 'hallowichig0/datetime_range_custom'```

4. You can clone the repository in the module/custom:
```git clone https://github.com/hallowichig0/datetime_range_custom.git```


MAINTAINERS
-----------

Current maintainers:
 * Jayson Garcia - https://www.drupal.org/u/hallow_ichig0

Credits to Yondu, Inc. & Globe Telecom for development time.
