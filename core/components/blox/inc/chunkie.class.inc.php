<?php
/*
 * Name: bloxChunkie
 * Original name: Chunkie
 * Version: 1.0
 * Author: Armand "bS" Pondman (apondman@zerobarrier.nl)
 * Date: Oct 8, 2006 00:00 CET
 * Modiefied for Revolution & bloX
 * 
 */

class bloxChunkie {

	var $template, $phx, $phxreq, $phxerror, $check;

	function bloxChunkie($template = '', $templates = array()) {
		$this->templates = & $templates;
		$this->template = $this->getTemplate($template);
		$this->depth = 0;
		$this->maxdepth = 4;
	}

	function CreateVars($value = '', $key = '', $path = '') {
		$this->depth++;
		if ($this->depth > $this->maxdepth) {
			return;
		}
		$keypath = !empty($path) ? $path . "." . $key : $key;
		echo $this->depth . ':' . $keypath . '<br/>';
		$this->placeholders[$keypath] = $value;
		return;

		if (is_array($value)) {
			foreach ($value as $subkey => $subval) {
				$this->CreateVars($subval, $subkey, $keypath);
				$this->depth--;
			}
		} else {
			$this->placeholders[$keypath] = $value;
		}
	}

	function AddVar($name, $value) {
		$this->placeholders[$name] = $value;
	}

	function Render() {
		global $modx;

		$template = $this->template;
		$chunk = $modx->newObject('modChunk');
		$chunk->setCacheable(false);
		$template = $chunk->process($this->placeholders, $template);
		unset($chunk);
		return $template;
	}

	function getTemplate($tpl) {
		// by Mark Kaplan
		global $modx;

		$template = "";
		if (isset($this->templates[$tpl])) {
			$template = $this->templates[$tpl];
		} else {
			if (substr($tpl, 0, 6) == "@FILE:") {
				$template = file_get_contents($modx->getOption('core_path') . substr($tpl, 6));
			} elseif (substr($tpl, 0, 6) == "@CODE:") {
				$template = substr($tpl, 6);
			} else {
				$chunk = $modx->getObject('modChunk', array('name' => $tpl), true);
				$template = ($chunk) ? $chunk->getContent() : FALSE;
			}
			$this->templates[$tpl] = $template;
		}

		return $template;
	}

}

?>
