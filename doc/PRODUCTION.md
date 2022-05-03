## Setting up the project in production

Once the code is properly set on the server, the environment variables configured
and that you ran _composer install_ with success, you need to follow the following
steps.

* Run the _make init_ command. It will create an empty DB.
* Install the assets as explained in the [developer documentation](./DEV.md)
* Create an admin user as explained in the [developer documentation](./DEV.md)
* You can now dump the DB and import it locally to migrate from your old blog.
