import json
import csv

tweets_data_path = 'twitterStream.txt'

tweets_data = []
tweets_file = open(tweets_data_path, "r")
for line in tweets_file:
    try:
        tweet = json.loads(line)
        #print(line)
        tweets_data.append(tweet)
    except:
        continue
#print (tweets_data[0])
for tweet in tweets_data:
        try:
            print(tweet['text'])
            print(tweet['geo'])
            print(tweet['coordinates'])
            print(tweet['user']['location'])
            print(tweet['lang'])
        except KeyError as ke:
            print(ke)
#print (len(tweets_data))