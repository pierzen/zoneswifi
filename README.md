Wifi Zones online map
=====================

This OpenLayers Dynamic POI map shows Wifi points from OpenStreetMap database. It only requires to develop the client side. The Overpass API service assures the server side work, extracting Wifi related POI's.

Zoom levels are limited to avoid extensive API requests.

To locate Wifi Spots
--------------------

Users locate towns to see the Wifi points. Once the search is completed, clusters of points are represented by circle and indiviudal Wifi points by a Wifi icon.

We plan to add a button to request for Wifi points inside the viewport. This would let users navigate through the map and then ask for Wifi points.

Technical Notes
=================

url on the online map : http://pierzen.dev.openstreetmap.org/zoneswifi/

Searches using Nominatim
------------------------

Users make Nominatim Search to locate cities. From this point, the viewport boundaries are calculated at zoom 11. An Overpass API request is made to extract the Wifi POI's for this bounding box.

Nominatim source code comes from OpenStreetMap user suncobalt.
http://wiki.openstreetmap.org/wiki/User:SunCobalt/OpenLayers_Suche

nominatim-i8n.csv Nominatim Results Translation file have been edited to add the french translation.


Wifi Layer Display Cluster Strategy
----------------------

The context function in the style_wifi_couche Style applies various rules based on number of points and zoom level. From zoom level 10 to 12, the size of clusters are bigger. From zoom level 13, 
 clusters provide the no. of Wifi points. Wifi icons represent individual points. From level 16, we have to take into account the fact that the POI's icons are added to the Mapnik layer.
 The size of the icon is increased to facilitate it's localisation. This could be replaced with a circle and larger strokeWidth. 

Limit the Vector Layer zoom levels
----------------------------------

Parameters such as minZoomLevl and maxZoomLevel are not enough to limit zoom levels with OpenLayers Vector Layer. There are many discussions on this subject and various solutions are proposed. We are using the parameters as below.

*	zoomOffset:10,
*	minZoomLevel: 10,
*	resolutions: [152.87405654907226, 76.4370282714844,38.2185141357422, 19.1092570678711, 9.55462853393555, 4.77731426696777, 2.38865713348389, 1.19432856674194]

License
-------
see https://github.com/pierzen/zoneswifi/blob/master/LICENSE

