/**
  * #uiname#组件，
  *
  */
define(["avalon", "text!./avalon.#uiname#.html"], function(avalon, tmpl) {

    var arr = tmpl.split("MS_OPTION_STYLE") || ["", ""]
    var cssText = arr[1].replace(/<\/?style>/g, "")
    var styleEl = document.getElementById("avalonStyle")
    var template = arr[0]
    try {
        styleEl.innerHTML += cssText
    } catch (e) {
        styleEl.styleSheet.cssText += cssText
    }

    var widget = avalon.ui.#uiname# = function(element, data, vmodels) {
        var options = data.#uiname#Options
        //方便用户对原始模板进行修改,提高制定性
        options.template = options.getTemplate(template, options)

        var vmodel = avalon.define(data.#uiname#Id, function(vm) {
            avalon.mix(vm, options)
            vm.widgetElement = element
            vm.$skipArray = ["widgetElement", "template"]

            var inited
            vm.$init = function() {
                if(inited) return
                inited = true

                avalon.scan(element, [vmodel].concat(vmodels))
            }
            vm.$remove = function() {
                element.innerHTML = element.textContent = ""
            }

            //@method apiName(argx) description

        })
      
        return vmodel
    }
    //add args like this:
    //argName: defaultValue, \/\/@param description
    //methodName: code, \/\/@optMethod optMethodName(args) description 
    widget.defaults = {
        getTemplate: function(tmpl, opts) {
            return tmpl
        },
        $author: "skipper@123"
    }
})