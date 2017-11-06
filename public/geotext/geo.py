import sys
import re
from geotext import GeoText

# places = GeoText("London is a great city")
# places.cities

# print(places.cities)
# # "London"

# GeoText('New York, Texas, and also China').country_mentions
# # OrderedDict([(u'US', 2), (u'CN', 1)])
# sys.argv[1]

#Validation for the comment

#text = "Question: Who, in their mind, goes to the Far East without first learning cultural and societal norms of that region? Ivanka Trump goes to a diplomatic meeting with the Prime Minister in Japan dressed in a pink short skirt suit. Anyone, that has ever traveled in and around the Far East knows that certain countries have very structured dress codes and diplomats must dress politely, such as skirt lengths to the knee or below. You never wear pink. The lady must be able to sit properly and not worry about being exposed. I would not doubt if Trump Continues his childish wrestling hand shakes that he crudely does. The Prime Minister should keep Trump away from his wife and any other female. Watch his tiny hands."
text = str(sys.argv[1])
filterPlaces = re.sub(' ', ',', text)

# print(sys.argv[1])

places = GeoText(str(filterPlaces))
print(places.countries + places.cities)