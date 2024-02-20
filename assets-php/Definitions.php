<?php
return [
  'WChat\Config' => \DI\autowire()->constructor(\DI\get('Config.ConfigFile'), true),
  'WChat\Request' => \DI\autowire()->constructor(\DI\get('RequestRaw'), true),
];
