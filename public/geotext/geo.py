from geotext import GeoText
import sys
import re


#testScript = "i love paris"


# sys.argv[1]

#print testScript.title()

#print testScript

# print titlePlaces

filterPlaces = re.sub('[^A-Za-z0-9[,]\s]+', '', sys.argv[1])



places = GeoText(filterPlaces)
places.cities
places.countries
print places.cities



# GeoText(sys.argv[1]).country_mentions
