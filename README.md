# message

Welcome to my open source PHP web application project on GitHub. It provided for users to upload files, users can also view and download the files online on web interface. To enhance user experience, the PHP-based project also supports file random playback and video online playback, with a ready-to-go Dockerfile provided for quick deployment.

## How to deploy

To access the application, you must configure your server's public directory to the ```public``` folder. You can then access ```message.php``` through your browser. Remember that your server must be configured properly for the application to function normally.

To install my project in Docker, you can use the ```Dockerfile``` and run the following command:

```docker build . -f Dockerfile .```

Once the build has completed successfully, the container can be started by typing

```docker run -p 80:80 -d <container_name>```

where <container_name> is the name that you have chosen for your container.
Once the build has been successful and the container has been spun up, the application can be accessed at the URL ```http://localhost:80/message.php``` . You can check the status of the container by typing
```docker ps```
and the name of the container should show up in the list of containers.

Go ahead and have fun with this example container!
