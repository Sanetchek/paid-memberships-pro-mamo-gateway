Authentication
Production
To get authenticated, you will first have to retreive your API key from the Mamo dashboard. You can do so by clicking here

Alternatively

log into your dashboard

navigate to the developer page

copy your API key
Now that you have your API key, it's time to get this tested

Bash

curl --location '<https://business.mamopay.com/manage_api/v1/me'>
  --header 'Authorization: Bearer <your_api_key>'
Sandbox
Still didn't get your own Sandbox account? Don't worry, we got you covered!

Reach out to us via chat by clicking on the chat bubble 💬 on the bottom right or via email: support@mamopay.com