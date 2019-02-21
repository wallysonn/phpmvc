php <?php

    require_once 'system/require.php';

    $pluginminname = "";

    do {
        echo "Nome do plugin:\n\n";
        $pluginName = fgets_u();
        if ($pluginName !== "") {
            echo "Plugin name is " . $pluginName."\n";
            $pluginminname = strtolower($pluginName);
            break;
        }

    } while (true);

    $output = '
    
    (function ($) {
        "use strict";

        var ' . $pluginName . ' = function (element, options, e) {

            if (e) {
                e.stopPropagation();
                e.preventDefault();
            }

            this.$element            = $(element);

            this.init();

        };

        ' . $pluginName . '.VERSION = "1.0";

        ' . $pluginName . '.DEFAULTS = {};

        ' . $pluginName . '.prototype = {

            constructor: ' . $pluginName . ',

            init  : function () {

            },
            events: function () {

            }

        };

        function Plugin(option, event) {            
            var args = arguments;            
            var _option = option,
                _event  = event;
            [].shift.apply(args);

            var value;
            var chain = this.each(function () {
                var $this = $(this);
                if ($this.is("div")) {
                    var data    = $this.data("' . $pluginminname . '"),
                        options = typeof _option == "object" && _option;

                    if (!data) {
                        var config      = $.extend({}, ' . $pluginName . '.DEFAULTS, $.fn.' . $pluginminname . '.defaults || {}, $this.data(), options);
                        config.template = $.extend({}, ' . $pluginName . '.DEFAULTS.template, ($.fn.' . $pluginminname . '.defaults ? $.fn.' . $pluginminname . '.defaults.template : {}), $this.data().template, options.template);
                        $this.data("' . $pluginminname . '", (data = new ' . $pluginName . '(this, config, _event)));
                    } else if (options) {
                        for (var i in options) {
                            if (options.hasOwnProperty(i)) {
                                data.options[i] = options[i];
                            }
                        }
                    }

                    if (typeof _option == "string") {
                        if (data[_option] instanceof Function) {
                            value = data[_option].apply(data, args);
                        } else {
                            value = data.options[_option];
                        }
                    }
                }
            });

            if (typeof value !== "undefined") {
                return value;
            } else {
                return chain;
            }
        }

        var old                     = $.fn.' . $pluginminname . ';
        $.fn.' . $pluginminname . '             = Plugin;
        $.fn.' . $pluginminname . '.Constructor = ' . $pluginName . ';

        $.fn.' . $pluginminname . '.noConflict = function () {
            $.fn.' . $pluginminname . ' = old;
            return this;
        };

    })(jQuery);';

    $fileOutPut = "$pluginName.js";

    $fp = fopen($fileOutPut, 'w');
    fwrite($fp, pack("CCC", 0xef, 0xbb, 0xbf)); //UTF-8 charset page
    fwrite($fp, $output);
    fclose($fp);

    //Remove Bom
    $fnRemoveBom = function ($str = "") {
        if (substr($str, 0, 3) == pack("CCC", 0xef, 0xbb, 0xbf)) {
            $str = substr($str, 3);
        }

        return $str;
    };

    $string = file_get_contents($fileOutPut);
    $string = $fnRemoveBom($string);
    file_put_contents($fileOutPut, $string);


    echo "Success\n";