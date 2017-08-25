#Import the necessary methods from tweepy library
from tweepy.streaming import StreamListener
from tweepy import OAuthHandler
from tweepy import Stream
import configparser
import sys

#Variables that contains the user credentials to access Twitter API 
keyword = sys.argv[1]
config = configparser.ConfigParser()
config.read("config.ini")
access_token = config.get('TwitterOAuth', 'TWITTEROAUTH_ACCESS_TOKEN')
access_token_secret = config.get('TwitterOAuth', 'TWITTEROAUTH_ACCESS_TOKEN_SECRET')
consumer_key = config.get('TwitterOAuth', 'TWITTEROAUTH_CONSUMER_KEY')
consumer_secret = config.get('TwitterOAuth', 'TWITTEROAUTH_CONSUMER_SECRET')


#This is a basic listener that just prints received tweets to stdout.
class StdOutListener(StreamListener):
    
    def __init__(self):
        self.tweetCount = 0

    def on_data(self, data):
        #Cancel after so many tweets --> This can be user defined
        self.tweetCount = self.tweetCount + 1
        if self.tweetCount > 1000:
            return False
        print (data)
        return True

    def on_error(self, status):
        print (status)


if __name__ == '__main__':

    #This handles Twitter authetification and the connection to Twitter Streaming API
    l = StdOutListener()
    auth = OAuthHandler(consumer_key, consumer_secret)
    auth.set_access_token(access_token, access_token_secret)
    stream = Stream(auth, l)

    #This line filter Twitter Streams to capture data by the keywords: 'python', 'javascript', 'ruby'
    #stream.filter(track=['python', 'javascript', 'ruby'])
    stream.filter(track=[keyword])