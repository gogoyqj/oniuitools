<?php
	$file = "database/db.txt";

	function getProjectList() {
		global $file;
		$all = explode("\n", file_get_contents($file));
		$res = array();
		$maxId = 1;
		foreach ($all as $key => $value) {
			$line = explode("\t", $value);
			if(count($line) >= 3) {
				array_push($res, array(
					"id"   => $line[0],
					"name" => $line[1],
					"path" => $line[2],
				));
				$maxId++;
			}
		}
		return array(
			"maxId" => $maxId,
			"list"  => $res,
		);
	}
	function addProject($name, $dir) {
		$all = getProjectList();
		array_push($all["list"], array(
			"id"   => $all["maxId"] + 1,
			"name" => $name,
			"path" => $dir,
		));
		writeDB($all["list"]);
	}
	function deteleProject($id) {
		$all = getProjectList();
		foreach ($all["list"] as $key => $value) {
			if($value["id"] == $id) {
				array_splice($all["list"], $key, 1);
				break;
			}
		}
		writeDB($all["list"]);
	}
	function changeProject($id, $name, $path) {
		$all = getProjectList();
		foreach ($all["list"] as $key => $value) {
			if($value["id"] == $id) {
				$all["list"][$key] = array(
					"id"   => $value["id"],
					"name" => $name,
					"path" => $path,
				);
				break;
			}
		}
		writeDB($all["list"]);
	}
	function writeDB($list) {
		global $file;
		$content = "";
		foreach ($list as $key => $value) {
			$content .= $value["id"] . "\t" . $value["name"] . "\t" . $value["path"] . "\n";
		}
		file_put_contents($file, $content);
	}

	function getUI($id, $flag=false) {
		$all = getProjectList();
		$tar = false;
		foreach ($all["list"] as $key => $value) {
			if($value["id"] == $id) {
				$tar = $value;
				break;
			}
		}
		if($tar) {
			$exeFile = explode("\\", __FILE__);
			$uiFile = explode("\\", $tar["path"]);
			$layer = 0;
			$prefix = "";

			foreach ($exeFile as $key => $value) {
				if(isset($uiFile[$key])) {
					if($uiFile[$key] === $value) {
						continue;
					}
				}
				$layer = $key;
				break;
			}
			$relativePath = implode(array_slice($uiFile, $layer), "/");
			$count = count($exeFile) - $layer;
			while($count > 1) {
				$prefix .= "../";
				$count--;
			}
			if($flag) {
				return pathinfo(trim($prefix . $relativePath));
			}
			return explode(" ", file_get_contents(trim($prefix . $relativePath)));
		}
		return array();
	}

	if(isset($_POST["action"])) {
		$action = $_POST["action"];
		$res = array("error" => 0);
		switch ($action) {
			case "del":
				# code...
				deteleProject($_POST["id"]);
				break;
			case "add":
				addProject($_POST["name"], $_POST["path"]);
				break;
			case "change":
				changeProject($_POST["id"], $_POST["name"], $_POST["path"]);
				break;
			default:
				# code...
				$r = getUI($_POST["id"], true);
				if($r) {
					$relativeDir = $r["dirname"] . "/";
					if($action == "create") {
						$cmd = "create";
						$uiname = $_POST["uiname"];
					} else {
						$cargs = explode(" ", $action);
						$cmd = $cargs[1];
						$uiname = $cargs[2];
					}
					$tooldir = "bin/";
					include "bin/ui.php";
				}
				break;
		}
		echo json_encode($res);
	}

?>