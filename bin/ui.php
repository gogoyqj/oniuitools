<?php
	if(isset($argv)) {
		$tooldir = dirname($argv[0]);
		if(isset($argv[1]) && isset($argv[2])) {
			$cmd = $argv[1];
			$uiname = $argv[2];
			$relativeDir = "";
		}
	}

	function _log($msg) {
		global $argv;
		if(isset($argv)) {
			exit($msg);
		} else {
			echo json_encode(array("error" => 0, "msg" => $msg));
			exit("");
		}
	}
	if(isset($tooldir)) {
		$dir = getcwd();
		$tpldir = $tooldir . "/../tpl/";
		if(isset($cmd) && isset($uiname)) {

			$rplarr = array($uiname);
			$hlarr = array("/#uiname#/m");

			$tdir = $relativeDir . $uiname . "/";
			$prefix = "avalon." . $uiname;

			$jstpl = preg_replace($hlarr, $rplarr, file_get_contents( $tpldir . "ui.tpl.js"));
			$tpl = preg_replace($hlarr, $rplarr, file_get_contents($tpldir . "ui.tpl.html"));
			$doctpl = preg_replace($hlarr, $rplarr, file_get_contents($tpldir . "ui.tpl.doc.html"));
			$extpl = preg_replace($hlarr, $rplarr, file_get_contents($tpldir . "ui.tpl.ex.html"));
			$sasstpl = preg_replace($hlarr, $rplarr, file_get_contents($tpldir . "ui.tpl.css"));

			if($cmd != "create" && !file_exists($tdir)) {
				_log($uiname . " is not found ~ -_-||\n");
			}

			// 创建组件
			if($cmd == "create") {
				if(!file_exists($tdir)) {
					mkdir($tdir);
					file_put_contents($tdir . $prefix . ".js", $jstpl);
					file_put_contents($tdir . $prefix . ".html", $tpl);
					file_put_contents($tdir . $prefix . ".doc.html", $doctpl);
					file_put_contents($tdir . $prefix . ".ex.html", $extpl);
					file_put_contents($tdir . $prefix . ".scss", $sasstpl);
					file_put_contents($tdir . $prefix . ".css", "");

					$myui = $relativeDir . "my.ui";
					if(file_exists($myui)) {
						$uis = explode(" ", file_get_contents($myui));
					} else {
						$uis = array();
					}
					$id = false;
					foreach ($uis as $key => $value) {
						if($value == $uiname) {
							$id = $value;
							break;
						}
					}
					if(!$id) {
						array_push($uis, $uiname);
						file_put_contents($myui, implode(" ", $uis));
					}
					_log($uiname . " is created succuss ~ ^_^\n");
				} else {
					_log($uiname . " is already created ~ -_-||\n");
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
						_log($argv[3] . ".html is not found ~ -_-||\n");
					}
					$res = file_get_contents($tdir . $prefix . "." . $argv[3] . ".html");
				}
				file_put_contents($tdir . $prefix . '.ex.' . $i . ".html", $res);

				_log("file " . $prefix . '.ex.' . $i . ".html" . " add succuss ~ ^_^\n");
			// 编译例子，把例子的代码自动写到pre里面
			} else if($cmd == "buildex") {
				$filename = $tdir . $prefix . ".ex.html";
				$i = 0;
				$s = "<pre ms-skip class=\"brush:html;gutter:false;toolbar:false\">";
				$e = "</pre>";
				while(file_exists($filename)) {
					$html = file_get_contents($filename);

					$front = explode($s, $html);
					$end = explode($e, $html);
					if(count($front) == 2 && count($end) == 2) {
						$f = $front[0];//preg_replace("/ ms\-controller=\"test\">/m", ">\n<div ms-controller=\"test\">", $front[0]);
						$n = preg_replace(array("/<script src=\"\.\.\/highlight\/shCore\.js\"><\/script>/m"), array(""), $f);
						preg_match("/ms\-controller=\"test\">/m", $n, $hehe);
						$se = $end[1];
						$res = $f . $s . htmlspecialchars( $n . $end[1] ) . $e . $se;

						file_put_contents($filename, $res);
					}

					$i++;
					$filename = $tdir . $prefix . ".ex." . $i . ".html";
				}
				_log("build " . $i . " examples ~ ^_^\n");
			// 生成文档，现在只做了一个生成例子列表的逻辑，之后接口说明什么的，可以考虑自动生成
			} else if($cmd == "builddoc") {
				$list = "";
				$filename = $tdir . $prefix . ".ex.html";
				$docname = $tdir . $prefix . ".doc.html";
				$i = 0;
				while(file_exists($filename)) {
					$html = file_get_contents($filename);
					$cname = preg_match("/(<h1[^>]*>)([^<]+)(<\/h1>)/m", $html, $cnamearr);
                    if(count($cnamearr) < 3) {
                        $i++;
                        $filename = $tdir . $prefix . ".ex." . $i .".html";
                        continue;
                    }
					$list .= '<li><a href="' . $prefix . ".ex." . ($i > 0 ? $i . "." : "") ."html" .'">' . preg_replace(array("/<h1>/", "/<\/h1>/"), array("",""), $cnamearr[2]) .'</a></li>' . "\n";
					$i++;
					$filename = $tdir . $prefix . ".ex." . $i .".html";
				}
				if(!file_exists($docname)) {
					//_log($docname . " is not found ~ -_-||\n");
					file_put_contents($docname, $doctpl);
				}
				$html = file_get_contents($docname);
				// 提取例子列表
				$s = "<!--ex list start-->";
				$e = "<!--ex list end-->";
				$front = explode($s, $html);
				$end = explode($e, $html);
				if(count($front) == 2 && count($end) == 2) {
					$res = $front[0] . $s . "\n<ol class=\"example-links\">\n" .$list . "</ol>\n" . $e . $end[1];
					file_put_contents($docname, $res);
					$html = $res;
				}
				// 提取重要方法说明
				$souceFile =  $tdir . $prefix . ".source.html";
				if(file_exists($souceFile)) {
					$s = "<!--source start-->";
					$e = "<!--source end-->";
					$front = explode($s, $html);
					$end = explode($e, $html);
					if(count($front) == 2 && count($end) == 2) {
						$res = $front[0] . $s . @file_get_contents($souceFile) . $e . $end[1];
						file_put_contents($docname, $res);
						$html = $res;
					}
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
						if($key != "description") {
							foreach ($value as $k => $v) {
								# code...
								$docHTML .= render($v);
							}
						}
					}
					$doc["description"] = preg_replace("/@description /", "", htmlspecialchars($doc["description"]));
					$res = $front[0] . $s . "\n" . $docHTML . $e . $end[1];
					if(preg_match("/<meta name=\"description\" content=[^\n]+/", $res)) {
						$res = preg_replace("/<meta name=\"description\" content=[^\n]+/", "<meta name=\"description\" content=\"" . $doc["description"] . "\"/>", $res);
					} else {
						$res = preg_replace("/<\/head>/", "<meta name=\"description\" content=\"" . $doc["description"] . "\"/>\n</head>", $res);
					}
					$res = preg_replace("/<fieldset class=\"doc-description\">[^\n]+/", "<fieldset class=\"doc-description\">" . $doc["description"] . "</fieldset>", $res);
					file_put_contents($docname, $res);
					$html = $res;
				}
				buildCss($uiname, "fromDoc");
				_log($docname . " is build succuss ~ ^_^\n");
			} else if($cmd == "buildcss") {
				buildCss($uiname);
			}
		}
		// help
		_log(file_get_contents($tpldir . "help.txt"));
	}
	function buildCss($tabName, $fromDoc=0) {
		system("sass " . $tabName . "/avalon." . $tabName . ".scss " . $tabName . "/avalon." . $tabName . ".css");
		if(!$fromDoc)_log("build css finished ~ ^_^\n");
	}
	function render($arr)
	{
		# code...
		return "<tr>\n    <td>" . $arr["name"] . "</td><td>" . (isset($arr["default"]) ? $arr["default"] : "") .  "</td><td>" . $arr["description"] . "</td></tr>\n";
	}
	function docGetter($content) {
		// 提取非defaults里面的接口说明
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
							"default" => "接口调用",
							"description" => preg_replace("/\/n/", "<br>", trim($u[1])))
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
            //提取页面其他地方的param
            preg_match_all("/([^\\/@\n]+\/\/@param[^\n]+)/", $defaults[0], $otherParam);
			$ps = explode("\n", $defaults[1]);
            if(isset($otherParam) && isset($otherParam[0])) {
                foreach($otherParam[0] as $k => $v) {
                    array_push($ps, $v);
                }
            }
			foreach ($ps as $key => $value) {
				# get method and options
				$doced = preg_match("/(\/\/@param)|(\/\/@optMethod)/", $value, $type);
				if($doced) {
					$u = explode($type[0], $value);
					if($type[0] == "//@param") {
						$f1 = explode(":", trim($u[0]));
                        if(count($f1) < 2) {
                            continue;
                        }
						array_push($optarr, array(
							"name" => preg_replace("/[^a-zA-Z0-9_$]+/", "", $f1[0]),
							"default" => preg_replace("/,/", "", $f1[1]),
							"description" => preg_replace("/\/n/", "<br>", $u[1])
						));
					} else if($type[0] == "//@optMethod") {
						$s = trim($u[1]);
						$name = preg_match("/^[a-zA-Z0-9_$]+/", $s, $fname);
						if($name) {
							array_push($optMethod, array(
								"name" => $fname[0],
								"default" => preg_match("/^on/m", $fname[0]) ? "回调函数" : "配置接口",
								"description" => preg_replace("/\/n/", "<br>", $s)
							));
						}
					}
				}
			}
		}
		// 提取description
		preg_match("/@description[^\n]+/", $content, $description);
		if(isset($description) && isset($description[0])) {
			$description = $description[0];
		} else {
			$description = 0;
		}

		return array(
			"arg" => $optarr,
			"argMethod" => $optMethod,
			"method" => $marr,
			"description" => $description
		);
	}


?>