<?php

while (($line = prompt('<? ')) !== false) {
	$line = trim($line);

	list($output, $result) = repl_eval($line);
	echo $output;
	show_result($result);
}

exit(0);

function prompt($prompt) {
	echo $prompt;
	flush();
	return fgets(STDIN);
}

function repl_eval($___code) {
	extract($GLOBALS, EXTR_REFS|EXTR_SKIP);
	ob_start();
	$___return = eval(fixup_code($___code));
	$___output = fixup_output(ob_get_clean());
	return array($___output, $___return);
}

function show_result($result) {
	echo "> ";
	var_dump($result);
}

function fixup_output($output) {
	if ($output === '') {
		return '';
	}
	return rtrim($output, "\n")."\n";
}

function fixup_code($code) {
	$tokens = token_get_all("<?php $code");
	array_shift($tokens);
	if (!$tokens) {
		return 'return null;';
	}
	if ($tokens[count($tokens)-1] === ';') {
		return $code;
	}

	$last_semicolon = (int) rfind_in($tokens, ';');

	if (!weird_builtin($tokens[$last_semicolon+1])) {
		array_splice($tokens, $last_semicolon+1, 0, array(' return '));
	}

	array_push($tokens, ' ;');

	return tokens_to_string($tokens);
}

function weird_builtin($token) {
	if (is_array($token)) {
		$t = $token[0];
		return in_array($t, array(T_ECHO, T_UNSET, T_GLOBAL));
	}
	return false;
}

function rfind_in($array, $needle) {
	if (!$array) {
		return false;
	}
	$l = count($array);
	for ($i = $l-1; $i >= 0; $i--) {
		if ($array[$i] === $needle) {
			return $i;
		}
	}
	return -1;
}
function tokens_to_string($tokens) {
	$str = '';
	foreach ($tokens as $t) {
		$str .= is_array($t)
			? $t[1]
			: $t;
	}
	return $str;
}
