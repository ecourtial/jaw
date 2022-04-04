<?php
/**
 * Factorize the code that could be used by other tests
 *
 * SCENARIO REGULAR:
 * - Creates a user with the command (in the CI).
 * - Connects to the admin with the credentials given to the command.
 * - Changes email and username.
 * - Read the token.
 * - Change password
 * - Logout.
 * - Connects again with the new credentials.
 * - Check that the token has not changed.
 * - Try to access configuration panel: get a 403
 * - Logout.
 */
