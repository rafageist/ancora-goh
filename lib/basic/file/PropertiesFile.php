<?php

/**
 * Reads "name = value" and "name[index] = value" configuration files
 * (bin/ancoragoh.conf, bin/security.conf).
 *
 * #14: values used to be executed with eval(), so a configuration file was
 * PHP code, and the array name of "name[index]" lost its last character
 * (substr($prop, 0, $p - 1)), so bin/security.conf was never applied.
 * Values are now parsed as PHP *literals* only: quoted strings, integers,
 * floats, true/false/null and array(...) / [...] of literals (optionally
 * with "key => value"). Anything else (variables, function calls,
 * operators, constants, interpolated strings) is rejected with a warning
 * and the line is ignored. Lines without "=" and names that are not
 * identifiers are ignored too.
 */
class PropertiesFile{

	var $properties;

	public function __construct($path){

		$this->properties = array();

		$f = @fopen($path,"r");
		if ($f === false) {
			trigger_error("PropertiesFile: cannot open configuration file", E_USER_WARNING);
			return;
		}

		while (!feof($f)){
			$s = fgets($f);
			if ($s === false) break;
			$s = trim($s);
			if ($s == "" || substr($s,0,1) == "#") continue;

			$eq = strpos($s,"=");
			if ($eq === false) continue;

			$prop = trim(substr($s,0,$eq));
			$text = trim(substr($s,$eq + 1));

			$ok = false;
			$value = self::parseLiteral($text, $ok);
			if (!$ok) {
				trigger_error("PropertiesFile: ignored non-literal value for '".preg_replace('/[^A-Za-z0-9_\[\]]/', '', $prop)."'", E_USER_WARNING);
				continue;
			}

			if (preg_match('/^([A-Za-z_][A-Za-z0-9_]*)\s*\[\s*(.*?)\s*\]$/', $prop, $m)) {
				$narr = $m[1];
				$index = $m[2];
				// accept name["index"] and name['index'] as well as name[index]
				if (strlen($index) >= 2 && ($index[0] == '"' || $index[0] == "'") && substr($index, -1) == $index[0])
					$index = substr($index, 1, -1);
				if (!isset($this->properties[$narr]) || !is_array($this->properties[$narr]))
					$this->properties[$narr] = array();
				$this->properties[$narr][$index] = $value;
			}
			else if (preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $prop)) {
				$this->properties[$prop] = $value;
			}
		}
		fclose($f);
	}

	/**
	 * Properties stay readable as $obj->name, as with the historical
	 * eval-created dynamic properties (e.g. $dbcfg->dbname in www/index.php).
	 */
	public function __get($name){
		return isset($this->properties[$name]) ? $this->properties[$name] : null;
	}

	public function __isset($name){
		return isset($this->properties[$name]);
	}

	/**
	 * Parse a PHP literal without executing it. $ok is set to false if the
	 * text is not a supported literal.
	 */
	public static function parseLiteral($text, &$ok){
		$ok = false;
		$list = array();
		foreach (token_get_all("<?php ".$text.";") as $t) {
			if (is_array($t)) {
				if ($t[0] == T_OPEN_TAG || $t[0] == T_WHITESPACE) continue;
				if ($t[0] == T_COMMENT || $t[0] == T_DOC_COMMENT) return null;
			}
			$list[] = $t;
		}
		$pos = 0;
		$value = self::parseValue($list, $pos, $ok, 0);
		if (!$ok) return null;
		// exactly one literal, followed by the ';' appended above
		if (!isset($list[$pos]) || $list[$pos] !== ';' || $pos + 1 != count($list)) {
			$ok = false;
			return null;
		}
		return $value;
	}

	private static function parseValue($list, &$pos, &$ok, $depth){
		$ok = false;
		if ($depth > 8 || !isset($list[$pos])) return null;
		$t = $list[$pos];

		$negative = false;
		if ($t === '-') {
			$negative = true;
			$pos++;
			if (!isset($list[$pos])) return null;
			$t = $list[$pos];
			if (!is_array($t) || ($t[0] != T_LNUMBER && $t[0] != T_DNUMBER)) return null;
		}

		if (is_array($t)) {
			switch ($t[0]) {
				case T_LNUMBER:
					$pos++; $ok = true;
					$v = intval(str_replace('_', '', $t[1]), 0);
					return $negative ? -$v : $v;
				case T_DNUMBER:
					$pos++; $ok = true;
					$v = (float) str_replace('_', '', $t[1]);
					return $negative ? -$v : $v;
				case T_CONSTANT_ENCAPSED_STRING:
					$pos++; $ok = true;
					return self::unquote($t[1]);
				case T_STRING:
					$w = strtolower($t[1]);
					if ($w == 'true' || $w == 'false' || $w == 'null') {
						$pos++; $ok = true;
						return $w == 'true' ? true : ($w == 'false' ? false : null);
					}
					return null;
				case T_ARRAY:
					$pos++;
					if (!isset($list[$pos]) || $list[$pos] !== '(') return null;
					$pos++;
					return self::parseArrayBody($list, $pos, $ok, ')', $depth);
			}
			return null;
		}
		if ($t === '[') {
			$pos++;
			return self::parseArrayBody($list, $pos, $ok, ']', $depth);
		}
		return null;
	}

	private static function parseArrayBody($list, &$pos, &$ok, $close, $depth){
		$ok = false;
		$result = array();
		while (true) {
			if (!isset($list[$pos])) return null;
			if ($list[$pos] === $close) { $pos++; $ok = true; return $result; }

			$item = self::parseValue($list, $pos, $ok, $depth + 1);
			if (!$ok) return null;
			$ok = false;

			if (isset($list[$pos]) && is_array($list[$pos]) && $list[$pos][0] == T_DOUBLE_ARROW) {
				$pos++;
				if (!is_int($item) && !is_string($item)) return null;
				$v = self::parseValue($list, $pos, $ok, $depth + 1);
				if (!$ok) return null;
				$ok = false;
				$result[$item] = $v;
			}
			else
				$result[] = $item;

			if (!isset($list[$pos])) return null;
			if ($list[$pos] === ',') { $pos++; continue; }
			if ($list[$pos] !== $close) return null;
		}
	}

	/**
	 * Decode a quoted PHP string literal. T_CONSTANT_ENCAPSED_STRING never
	 * contains variable interpolation. Single quotes: \\ and \' only.
	 * Double quotes: \\ \" \n \t \r \$; other escapes are kept literally.
	 */
	private static function unquote($lit){
		$q = $lit[0];
		$body = substr($lit, 1, -1);
		if ($q == "'")
			return strtr($body, array("\\\\" => "\\", "\\'" => "'"));
		return strtr($body, array("\\\\" => "\\", "\\\"" => "\"", "\\n" => "\n", "\\t" => "\t", "\\r" => "\r", "\\\$" => "\$"));
	}

	public function asString($separator = " "){
		$s = "";
		$primero = true;
		foreach ($this->properties as $key => $value){
			if (is_array($value)) $value = "array(".count($value).")";
			$s .= ($primero==false?$separator:"")."$key = $value";
			$primero = false;
		}
		return $s;
	}
}

?>
