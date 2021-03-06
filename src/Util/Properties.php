<?php
namespace Phruts\Util;

/**
 * A set of persistent properties, which can be loaded from a properties file.
 */
class Properties
{
    /**
	 * @var array
	 */
    protected $properties = array ();

    /**
	 * Adds the given key/value pair to this properties.
	 *
	 * @param string $key
	 * @param string $value
	 */
    public function setProperty($key, $value)
    {
        $key = (string) $key;
        $value = (string) $value;

        $this->properties[$key] = $value;
    }

    /**
	 * Reads a property list from a properties file.
	 *
	 * <p>The stream should have the following format:</p>
	 * <p>An empty line or a line starting with <samp>#</samp> or <samp>!</samp>
	 * is ignored. A backslash (<samp>\</samp>) at the end of the line makes the
	 * line continueing on the next line (but make sure there is no whitespace
	 * after the backslash). Otherwise, each line describes a key/value pair.</p>
	 * <p>The chars up to the first whitespace, "=" or ":" are the key. You can
	 * include this caracters in the key, if you precede them with a backslash
	 * (<samp>\</samp>). The key is followed by optional whitespaces, optionally
	 * one <samp>=</samp> or <samp>:</samp>, and optionally some more whitespaces.
	 * The rest of the line is the resource belonging to the key.</p>
	 * <p>Escape sequences <samp>\t, \n, \r, \\, \", \', \!, \#, \</samp> (a
	 * space) are detected and converted to the corresponding single
	 * character.</p>
	 * <pre>
	 * # This is a comment
	 * key     = value
	 * k\:5      \ a string starting with space and ending with newline\n
	 * # This is a multiline specification; note that the value contains
	 * # no white space.
	 * weekdays: Sunday,Monday,Tuesday,Wednesday,\\
	 * Thursday,Friday,Saturday
	 * </pre>
	 *
	 * @param string $file The properties file
	 * @throws \Phruts\Exception\IOException
	 */
    public function load($file)
    {
        $handle = @ fopen($file, 'r', true);

        if ($handle) {
            while (!feof($handle)) {
                $buffer = stream_get_line($handle, 1024, "\n");
                $buffer = str_replace("\r", '', $buffer);
                $bufferLength = strlen($buffer);

                $pos = 0;
                // Leading whitespaces must be deleted first
                while (($pos < $bufferLength) && (($c = substr($buffer, $pos, 1)) == ' ')) {
                    $pos++;
                }

                // If empty line or begins with a comment character, skip this line
                if (($pos == $bufferLength) || ($c == '#') || ($c == '!')) {
                    continue;
                }

                // The characters up to the next whitespace, ':', or '=' describe
                // the key. But look for escape sequences.
                $key = '';
                while (($pos < $bufferLength) && (($c = substr($buffer, $pos++, 1)) != ' ') && ($c != '=') && ($c != ':')) {
                    if ($c == '\\') {
                        if ($pos == $bufferLength) {
                            // The line continues on the next line. If there is no next line,
                            // just treat it as a key with an empty value
                            $buffer = stream_get_line($handle, 1024, "\n");
                            if ($buffer === false) {
                                // We might have seen a backslash at the end of the file.
                                // We ignores the backslash in this case
                                break 2;
                            }

                            $buffer = str_replace("\r", '', $buffer);
                            $bufferLength = strlen($buffer);
                            $pos = 0;
                            while (($pos < $bufferLength) && (($c = substr($buffer, $pos, 1)) == ' ')) {
                                $pos++;
                            }
                        } else {
                            $c = substr($buffer, $pos++, 1);
                            switch ($c) {
                                case 'n' :
                                    $key .= "\n";
                                    break;
                                case 't' :
                                    $key .= "\t";
                                    break;
                                case 'r' :
                                    $key .= "\r";
                                    break;
                                default :
                                    $key .= $c;
                                    break;
                            }
                        }
                    } else {
                        $key .= $c;
                    }
                }

                $isDelim = ($c == ':') || ($c == '=');
                while (($pos < $bufferLength) && (($c = substr($buffer, $pos, 1)) == ' ')) {
                    $pos++;
                }

                if (!$isDelim && (($c == ':') || ($c == '='))) {
                    $pos++;
                    while (($pos < $bufferLength) && (($c = substr($buffer, $pos, 1)) == ' ')) {
                        $pos++;
                    }
                }

                $element = '';
                while ($pos < $bufferLength) {
                    $c = substr($buffer, $pos++, 1);
                    if ($c == '\\') {
                        if ($pos == $bufferLength) {
                            // The line continues on the next line
                            $buffer = stream_get_line($handle, 1024, "\n");
                            // We might have seen a backslash at the end of the file.
                            // We ignores the backslash in this case
                            if ($buffer === false) {
                                break;
                            }

                            $buffer = str_replace("\r", '', $buffer);
                            $bufferLength = strlen($buffer);
                            $pos = 0;
                            while (($pos < $bufferLength) && (($c = substr($buffer, $pos, 1)) == ' '))
                                $pos++;
                        } else {
                            $c = substr($buffer, $pos++, 1);
                            switch ($c) {
                                case 'n' :
                                    $element .= "\n";
                                    break;
                                case 't' :
                                    $element .= "\t";
                                    break;
                                case 'r' :
                                    $element .= "\r";
                                    break;
                                default :
                                    $element .= $c;
                                    break;
                            }
                        }
                    } else {
                        $element .= $c;
                    }
                }

                $this->properties[$key] = $element;
            }
            fclose($handle);
        } else {
            $err = error_get_last();
            throw new \Phruts\Exception\IOException('Cannot open file "' . $file . '": ' . $err['message']);
        }
    }

    /**
	 * Returns the number of properties
	 *
	 * @var integer
	 */
    public function size()
    {
        return count($this->properties);
    }

    /**
	 * Returns the keys of properties
	 *
	 * @return array
	 */
    public function keySet()
    {
        return array_keys($this->properties);
    }

    /**
	 * Gets the property with the specified key in the property list.
	 *
	 * @param string $key The key for this property
	 * @return string The value for the given key or null if not found
	 */
    public function getProperty($key)
    {
        if (array_key_exists($key, $this->properties)) {
            return $this->properties[$key];
        } else {
            return null;
        }
    }
}
