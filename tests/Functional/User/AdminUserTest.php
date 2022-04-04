<?php
/**
 * Factorize the code that could be used by other tests
 *
 * SCENARIO ADMIN:
 * - Creates a user with the command (done in the CI).
 * - Connects to the admin with the credentials given to the command.
 * - Changes email and username.
 * - Read the token.
 * - Change password
 * - Logout.
 * - Connects again with the new credentials.
 * - Check that the token has not changed.
 * - Try to access configuration panel: change all the values
 * - Logout.
 * - Connect again.
 * - Check the configuration values: they have been properly updated
 */


class 
