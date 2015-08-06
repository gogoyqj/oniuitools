/**
 * @cnName 这是一个神奇的组件
 * @enName #uiname#
 * @introduce
 *    <p></p>
 */
define(["avalon", "text!./avalon.#uiname#.html", "css!./avalon.#uiname#.css", "css!../chameleon/oniui-common.css"], function(avalon, template) {

    var widget = avalon.ui.#uiname# = function(element, data, vmodels) {
        var options = data.#uiname#Options
        //方便用户对原始模板进行修改,提高定制性
        options.template = options.getTemplate(template, options)

        var vmodel = avalon.define(data.#uiname#Id, function(vm) {
            avalon.mix(vm, options)
            vm.widgetElement = element
            vm.$skipArray = ["widgetElement", "template"]

            var inited
            vm.$init = function(continueScan) {
                if(inited) return
                inited = true

                if (continueScan) {
                    continueScan()
                } else {
                    avalon.log("avalon请尽快升到1.3.7+")
                    avalon.scan(element, [vmodel].concat(vmodels))
                    if (typeof options.onInit === "function") {
                        options.onInit.call(element, vmodel, options, vmodels)
                    }
                }
            }
            vm.$remove = function() {
                element.innerHTML = element.textContent = ""
            }

        })
      
        return vmodel
    }
    widget.defaults = {
        //@config {Function} onInit(vmodel, options, vmodels) 完成初始化之后的回调,call as element's method
        onInit: avalon.noop,
        getTemplate: function(tmpl, opts, tplName) {
            return tmpl
        },//@config {Function} getTemplate(tpl, opts, tplName) 定制修改模板接口
        $author: "skipper@123"
    }
})