<?php
	if(isset($argv)) {
		$tooldir = dirname($argv[0]);
		$dir = getcwd();
		$tpldir = $tooldir . "/../tpl/";
		if(isset($argv[1]) && isset($argv[2])) {
			$cmd = $argv[1];
			$uiname = $argv[2];

			$rplarr = array($uiname);
			$hlarr = array("/#uiname#/m");

			$tdir = $uiname . "/";
			$prefix = "avalon." . $uiname;

			$jstpl = preg_replace($hlarr, $rplarr, file_get_contents( $tpldir . "ui.tpl.js"));
			$tpl = preg_replace($hlarr, $rplarr, file_get_contents($tpldir . "ui.tpl.html"));
			$doctpl = preg_replace($hlarr, $rplarr, file_get_contents($tpldir . "ui.tpl.doc.html"));
			$extpl = preg_replace($hlarr, $rplarr, file_get_contents($tpldir . "ui.tpl.ex.html"));

			if($cmd != "create" && !file_exists($uiname)) {
				exit($uiname . " is not found ~ -_-||");
			}

			// 创建组件
			if($cmd == "create") {
				if(!file_exists($uiname)) {
					mkdir($uiname);
					file_put_contents($tdir . $prefix . ".js", $jstpl);
					file_put_contents($tdir . $prefix . ".html", $tpl);
					file_put_contents($tdir . $prefix . ".doc.html", $doctpl);
					file_put_contents($tdir . $prefix . ".ex.html", $extpl);
					exit($uiname . " is created succuss ~ ^_^");
				} else {
					exit($uiname . " is already created ~ -_-||");
				}
			// 添加例子
			} else if($cmd == "addex") {
				$i = 1;
				while(file_exists($tdir . $prefix . '.ex.' . $i . ".html")) {
					$i++;
				}
				$res = $extpl;
				if(isset($argv[3])) {
					if(!file_exists($tdir . $prefix . "." . $argv[3] . ".html")) {
						die($argv[3] . ".html is not found ~ -_-||");
					}
					$res = file_get_contents($tdir . $prefix . "." . $argv[3] . ".html");
				}
				file_put_contents($tdir . $prefix . '.ex.' . $i . ".html", $res);

				exit("file " . $prefix . '.ex.' . $i . ".html" . " add succuss ~ ^_^");
			// 编译例子，把例子的代码自动写到pre里面
			} else if($cmd == "buildex") {
				$filename = $tdir . $prefix . ".ex.html";
				$i = 0;
				$s = "<pre class=\"brush:html;gutter:false;toolbar:false\">";
				$e = "</pre>";
				while(file_exists($filename)) {
					$html = file_get_contents($filename);

					$front = explode($s, $html);
					$end = explode($e, $html);
					if(count($front) == 2 && count($end) == 2) {
						$n = preg_replace(array("/<script src=\"\.\.\/highlight\/shCore\.js\"><\/script>/m"), array(""), $front[0]);
						$res = $front[0] . $s . htmlspecialchars( $n . $end[1] ) . $e . $end[1];

						file_put_contents($filename, $res);
					}

					$i++;
					$filename = $tdir . $prefix . ".ex." . $i . ".html";
				}
				exit("build " . $i . " examples ~ ^_^");
			// 生成文档，现在只做了一个生成例子列表的逻辑，之后接口说明什么的，可以考虑自动生成
			} else if($cmd == "builddoc") {
				$list = "";
				$filename = $tdir . $prefix . ".ex.html";
				$docname = $tdir . $prefix . ".doc.html";
				$i = 0;
				while(file_exists($filename)) {
					$html = file_get_contents($filename);
					$cname = preg_match("/<h1>[^<]+<\/h1>/m", $html, $cnamearr);
					$list .= '<li><a href="' . $prefix . ".ex." . ($i > 0 ? $i . "." : "") ."html" .'">' . preg_replace(array("/<h1>/", "/<\/h1>/"), array("",""), $cnamearr[0]) .'</a></li>' . "\n";
					$i++;
					$filename = $tdir . $prefix . ".ex." . $i .".html";
				}
				if(!file_exists($docname)) {
					exit($docname . " is not found ~ -_-||");
				}
				$html = file_get_contents($docname);
				// 提取例子列表
				$s = "<!--ex list start-->";
				$e = "<!--ex list end-->";
				$front = explode($s, $html);
				$end = explode($e, $html);
				if(count($front) == 2 && count($end) == 2) {
					$res = $front[0] . $s . "\n<ol>\n" .$list . "</ol>\n" . $e . $end[1];
					file_put_contents($docname, $res);
					$html = $res;
				}
				// 提取参数、接口文档说明
				// 提取例子列表
				$s = "<!--auto doc start-->";
				$e = "<!--auto doc end-->";
				$front = explode($s, $html);
				$end = explode($e, $html);
				if(count($front) == 2 && count($end) == 2) {
					$docHTML = '';
					$js = file_get_contents($tdir . $prefix . ".js");
					$doc = docGetter($js);
					foreach ($doc as $key => $value) {
						# code...
						foreach ($value as $k => $v) {
							# code...
							$docHTML .= render($v);
						}
					}
					$res = $front[0] . $s . "\n" . $docHTML . $e . $end[1];
					file_put_contents($docname, $res);
					$html = $res;
				}
				exit($docname . " is build succuss ~ ^_^");
			}
		}
		// help
		echo file_get_contents($tpldir . "help.txt");
	}
	function render($arr)
	{
		# code...
		return "<tr>\n    <td>" . $arr["name"] . "</td><td>" . (isset($arr["default"]) ? $arr["default"] : "") .  "</td><td>" . $arr["description"] . "</td></tr>\n";
	}
	function docGetter($content) {
		// 提取非defaus里面的接口说明
		preg_match_all("/\/\/@method[^\n]+/", $content, $methods);
		$marr = array();
		if(isset($methods) && isset($methods[0])) {
			foreach ($methods[0] as $key => $value) {
				$u = explode("//@method", $value);
				if(count($u) == 2) {
					preg_match("/[a-zA-Z0-9_$]+/", trim($u[1]), $names);
					if(isset($names)) {
						array_push($marr, array(
							'name' => $names[0], 
							"default" => "接口函数",
							"description" => trim($u[1]))
						);
					}
				}
			}
		}
		// 提取defaults里面的说明
		$defaults = explode("widget.defaults = {", $content);
		$optarr = array();
		$optMethod = array();
		if(count($defaults) == 2) {
			$ps = explode("\n", $defaults[1]);
			foreach ($ps as $key => $value) {
				# get method and options
				$doced = preg_match("/(\/\/@param)|(\/\/@optMethod)/", $value, $type);
				if($doced) {
					$u = explode($type[0], $value);
					if($type[0] == "//@param") {
						$f1 = explode(":", trim($u[0]));
						array_push($optarr, array(
							"name" => preg_replace("/[^a-zA-Z0-9_$]+/", "", $f1[0]),
							"default" => preg_replace("/,/", "", $f1[1]),
							"description" => $u[1]
						));
					} else if($type[0] == "//@optMethod") {
						$s = trim($u[1]);
						$name = preg_match("/^[a-zA-Z0-9_$]+/", $s, $fname);
						if($name) {
							array_push($optMethod, array(
								"name" => $fname[0],
								"default" => "配置函数",
								"description" => $s
							));
						}
					}
				}
			}
		}
		return array(
			"arg" => $optarr,
			"argMethod" => $optMethod,
			"method" => $marr
		);
	}


?>