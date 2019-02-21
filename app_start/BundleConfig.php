<?php

    class BundleConfig
    {

        public static function registerBundles()
        {
            $bundle = new BundleCollection();

            $bundle->enableOptimizations = false; //Enabled optimizations
            $bundle->minityHtml = false; //Format inline the html code
            $bundle->showComment = true;

        }
    }