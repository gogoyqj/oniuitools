<?php
	include "api.php";
	$all = getProjectList();
	$id = isset($_GET["id"]) ? intval($_GET["id"]) : false;
	$ui = getUI($id);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="content-type" content="text/html;charset=utf-8" />
        <meta http-equiv="x-ua-compatible" content="ie=edge" />
        <title>oniui tools</title>
        <style type="text/css">
        	ul{
        		border: 1px solid #ccc;
        		margin: 0;
        		padding: 0;
        		font-size:13px;
        	}
			li{
				list-style: none;
				padding: 8px;
				border-bottom: 1px dashed #ccc;
			}
			li.last{
				border-bottom: none;
			}
			li.add{
				border-bottom: none;
				border-top: 1px dashed #ccc;
			}
			li.select{
				background: #dedede;
			}
			form span{
				width: 50px;
				display: inline-block;
			}
			a, a:link, a:hover{
				text-decoration: none;
				color: #000;
			}
			.ui-menu span{
				width: 80px;
				display: inline-block;
			}
        </style>
        <script src="http://hotelued.qunar.com/static/js/avalon.js"></script>
        <script src="avalon.js"></script>
        <script src="jq.js"></script>
    	<script type="text/javascript">
    		var uiVmodel = avalon.define("ui", function(vm) {
    			vm.projects = <?php echo json_encode($all);?>;
    			vm.ui = <?php echo json_encode($ui);?>;
    			vm.select = <?php echo $id ? $id : 0;?>;
    			vm.getData = function(ele, cmd) {
    				var input = ele.getElementsByTagName("input"),
    					res="",
    					error
    				avalon.each(input, function(i, item) {
    					var n
    					if(n = item.getAttribute("name")) {
    						if(!item.value.trim()) {
    							error = n + " can not be empty"
    						}
    						res += "&" + n + "=" + item.value.trim()
    					}
    				})
    				if(error) {
    					alert(error)
    				} else {
    					// jQuery.post("api.php", res, function(r) {
    					// 	console.log(r)
    					// 	alert(r.msg || "success")
    					// })
    					if(cmd.match(/ui addex/g)) {
    						var base = prompt("基于例子文件（如ex，ex.1）：")
    						base = $.trim(base)
    						if(base) {
    							cmd += " " + base
    						}
    					}
    					res = cmd + res
    					res = res.replace(/&$/g, "")
    					jQuery.ajax({
    						url:"api.php",
    						type: "POST",
    						dataType: "json",
    						data: res,
    						success: function(r) {
    							alert(r.msg || "finished!")
    						}
    					})
    				}
    			}
    		})
    	</script>
    </head>
    <body>
    	<div class="ms-controller" ms-controller="ui">
			<ul id="list" class="pu" style="width:800px;">
				<li class="pl" ms-repeat-item="projects.list" ms-class-103="select:select==item.id">
					<form submit="return false">
						<span>id:{{item.id}}</span>
						<input name="id" type="hidden" ms-value="item.id"/>
						<input name="name" type="text" ms-value="item.name"/>
						<input name="path" style="width:300px;" type="text" ms-value="item.path"/>
						<input type="button" data-cmd="change" value="提交修改"/>
						<input type="button" data-cmd="del" value="删除"/>
						<a ms-href="'?id='+item.id">切换到</a>
					</form>
					<ul class="ui-menu" ms-if="select==item.id" style="background:#fff;">
						<li ms-repeat-ui="ui">
							<span>{{ui}}</span>
							<input type="button" ms-data-cmd="'ui buildcss '+ui"value="buildCss"/>
							<input type="button" ms-data-cmd="'ui builddoc '+ui"value="buildDoc"/>
							<input type="button" ms-data-cmd="'ui addex '+ui"value="addEx"/>
							<input type="button" ms-data-cmd="'ui buildex '+ui"value="buildEx"/>
							<input type="hidden" name="id" ms-value="item.id"/>
						</li>
						<li>
							<form ms-submit="submit">
							<span>创建:</span>
							<input placeholder="ui name" type="text" name="uiname"/>
							<input type="button" data-cmd="create" value="新增"/>
							<input type="hidden" name="id" ms-value="item.id"/>
						</li>
					</ul>
				</li>
				<li class="pl" ms-class-102="add">
					<form submit="return false">
						<span>新增:</span>
						<input placeholder="project name" name="name" type="text"/>
						<input placeholder="my.ui file path" name="path" style="width:300px;" type="text"/>
						<input data-cmd="add" type="button" value="新增"/>
					</form>
				</li>
			</ul>
    	</div>
    </body>
    <script type="text/javascript">
    	avalon(document.getElementById("list")).bind("click", function(e) {
    		var tar = e.target || e.srcElement,
    			cmd = tar.getAttribute("data-cmd");
    		if(cmd) {
    			e.preventDefault();
    			var par = tar.parentNode;
    			uiVmodel.getData(par, "action=" + cmd)
    			if(cmd in {"create": 1, "change": 1, "del": 1, "add": 1}) {
    				location.reload()
    			}
    		}
    	})
    </script>
</html>