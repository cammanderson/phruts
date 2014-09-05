<?php

namespace Phruts\Util;

/**
 * TokenProcessor is responsible for handling all token related functionality.  The
 * methods in this class are synchronized to protect token processing from multiple
 * threads.  ActionKernel containers are allowed to return a different HttpSession
 * object for two threads accessing the same session so it is not possible to
 * synchronize on the session.
 *
 * @author Cameron MANDERSON <cameronmanderson@gmail.com> (Phruts Contibutor)
 * @since Struts 1.1 */
class TokenProcessor
{
    /**
     * Retrieves the singleton instance of this class.
     * @return TokenProcessor
     */
    public static function getInstance()
    {
        static $instance;
        if(empty($instance)) $instance = new \Phruts\Util\TokenProcessor();

        return $instance;
    }

//	private function __construct() {
//
//	}

    /**
     * The timestamp used most recently to generate a token value.
     */
    private $previous;

    /**
     * Return <code>true</code> if there is a transaction token stored in
     * the user's current session, and the value submitted as a request
     * parameter with this action matches it.  Returns <code>false</code>
     * <ul>
     * <li>No session associated with this request</li>
     * <li>No transaction token saved in the session</li>
     * <li>No transaction token included as a request parameter</li>
     * <li>The included transaction token value does not match the
     *     transaction token in the user's session</li>
     * </ul>
     *
     * @param \Symfony\Component\HttpFoundation\Request request The actionKernel request we are processing
     * @param reset Should we reset the token after checking it?
     */
    public function isTokenValid(\Symfony\Component\HttpFoundation\Request $request, $reset = false)
    {
        // Retrieve the current session for this request
        $session = $request->getSession(false); // \Symfony\Component\HttpFoundation\Session\Session
        if ($session == null) {
            return false;
        }

        // Retrieve the transaction token from this session, and
        // reset it if requested
        $saved = $session->get(\Phruts\Globals::TRANSACTION_TOKEN_KEY);
        if ($saved == null) {
            return false;
        }

        if ($reset) {
            $this->resetToken($request);
        }

        // Retrieve the transaction token included in this request
        $token = $request->get(\Phruts\Globals::TOKEN_KEY);
        if ($token == null) {
            return false;
        }

        return ($saved == $token);
    }

    /**
     * Reset the saved transaction token in the user's session.  This
     * indicates that transactional token checking will not be needed
     * on the next request that is submitted.
     *
     * @param \Symfony\Component\HttpFoundation\Request request The actionKernel request we are processing
     */
    public function resetToken(\Symfony\Component\HttpFoundation\Request $request)
    {
        $session = $request->getSession(false); // \Symfony\Component\HttpFoundation\Session\Session
        if ($session == null) {
            return;
        }
        $session->remove(\Phruts\Globals::TRANSACTION_TOKEN_KEY);
    }

    /**
     * Save a new transaction token in the user's current session, creating
     * a new session if necessary.
     *
     * @param \Symfony\Component\HttpFoundation\Request request The actionKernel request we are processing
     */
    public function saveToken(\Symfony\Component\HttpFoundation\Request $request)
    {
        $session = $request->getSession(); //\Symfony\Component\HttpFoundation\Session\Session
        $token = $this->generateToken($request);
        if ($token != null) {
            $session->set(\Phruts\Globals::TRANSACTION_TOKEN_KEY, $token);
        }
    }

    /**
     * Generate a new transaction token, to be used for enforcing a single
     * request for a particular transaction.
     *
     * @param \Symfony\Component\HttpFoundation\Request request The request we are processing
     * @return String
     */
    public function generateToken(\Symfony\Component\HttpFoundation\Request $request)
    {
        $session = $request->getSession(); //\Symfony\Component\HttpFoundation\Session\Session
        $id = $session->getId();
        $current = microtime();
        if ($current == $this->previous) {
            $current++;
        }
        $this->previous = $current;

        return md5($id . $current);
    }
}
