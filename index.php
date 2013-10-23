<?php  /* zonesWIFI */  ?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
  <title>Zones Wifi</title>
  <meta name="viewport" content="width=device-width" />
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="author" content="Pierre Béland">
  <link rel="shortcut icon" type="image/x-icon" href="img/favicon.ico">
  <script type="application/javascript" src="http://openlayers.org/api/2.13/OpenLayers.js"></script>
<!--
  <script type="application/javascript" src="http://openlayers.org/api/2.13/OpenLayers.mobile.js"></script>
-->
  <script type="application/javascript" src="js/fr.js"></script>
  <script type="application/javascript" src="OpenStreetMap.js"></script>
  <script type="application/javascript" src="overpass.js"></script>
  <script type="application/javascript" src="OSMMeta.js"></script>
  <script type="application/javascript" src="LoadingPanel.js"></script>
  <script type="application/javascript">
//<![CDATA[
/*
	copyright : Pierre Béland
	octobre 2013
	sources : https://github.com/pierzen/zoneswifi/
	
*/
var lat = 45.5222;
var lon = -73.7186;
var zoom = 10;
var map;
var fenetre_popup;
// controle affichage des marqueurs
// nb dans cercles à partir de zoom=13
var zoom_nb=3;
// icones plus gros et plus clair à partir de zoom=16
var zoom_icone_plus=5;

var wifi_region;
var largeur_popup,hauteur_popup;
// panneau glissant gauche affiché  au départ
var showpanel=0;

var coord_region=new Array();
coord_region["montreal"]={zoom:10,lat:45.5222,lon:-73.7186}
coord_region["sainte-agathe"]={zoom:10,lat:46.1332,lon:-74.0630}
coord_region["quebec"]={zoom:10,lat:46.8226,lon:-71.1598}
coord_region["trois-rivieres"]={zoom:10,lat:46.4596,lon:-72.6787}
coord_region["sherbrooke"]={zoom:10,lat:45.3965,lon:-72.1527}
console.log("sherbrooke zoom "+coord_region["sherbrooke"].zoom);
var url_trois_rivieres="";
var url_est="";
var url_saguenay="";
var url_outaouais="";
var url_abitibi="";

if (document.body)
{
var largeur_ecran = (document.body.clientWidth);
var hauteur_ecran = (document.body.clientHeight);
} 	
if (largeur_ecran<=640) {
	largeur_popup=250;hauteur_popup=150;
	}
	else {largeur_popup=300;hauteur_popup=200;
	}

    function fenetre_popup_info() {
		var fenetre_popup=window.open("zoneswifi_info.html",
		"pop1","width=200,height=200");
		fenetre_popup.document.close();
		fenetre_popup.focus();
		onblur="window.focus()";
    }
// apres recherche nominatimm, - demande extraction selon lon,lat,zoom --> bbox calculé
function selection_wifi_lonlat(lon,lat,zoom) {

	console.log("sel_wifi lon,lat,zoom "+lon+", "+lat+",",+zoom);
	zoom=zoom-10;if (zoom<0) {zoom=0;}
	for (nb in map.layers) {
		if (nb > 0) {
			console.log("enlève couche wifi précédente, no "+nb);
			map.layers[nb].setVisibility(false);
			map.removeLayer(map.layers[nb], false);
		}
	}
    var lonlat_reg = new OpenLayers.LonLat(lon, lat).transform(new OpenLayers.Projection("EPSG:4326"), new OpenLayers.Projection("EPSG:900913"));
    map.setCenter (lonlat_reg, zoom);
    //bounds.left, .bottom, .right, .top
    var bounds = new OpenLayers.Bounds();
	bounds=map.getExtent();
	var WGS84 = new OpenLayers.Projection("EPSG:4326");
	bounds.transform(map.getProjectionObject(), WGS84);
	var bbox=String(bounds.bottom.toFixed(5)+","+bounds.left.toFixed(5)+","+bounds.top.toFixed(5)+","+bounds.right.toFixed(5));
	console.log("bounds "+ bounds+" -> l "+bounds.left+",b "+bounds.bottom+",r "+bounds.right+",t "+bounds.top);
	console.log("bbox "+ bbox);
	var url1="http://overpass-api.de/api/interpreter?data=[timeout:30];node[internet_access=wlan](";
	var url3=");out+meta;(way[internet_access=wlan](";
	var url5=");node(w););out+meta;";
	var url_lonlat=url1+bbox+url3+bbox+url5;
	console.log("url_lonlat "+url_lonlat);
	//----------	
	couche_wifi (couche=wifi_region,
	desc="Points Wifi", url=url_lonlat, affichage=true);
	
}
		
function afficher_contribuez() {
	var contribuez=document.getElementById("contribuez");
	console.log("contribuez="+contribuez.className);
	if (contribuez.className=="cacher") {contribuez.className="cadre l90"}
	else {contribuez.className="cacher"}
	console.log("contribuez=>"+contribuez.className);
}
		
function SlidePanel()
{
   leftpanel = document.getElementById('section_gauche');
   contribuez = document.getElementById('contribuez');
   controlpanel = document.getElementById('control_panel');
   controltxt = document.getElementById('controltxt');
   controlbtn = document.getElementById('controlbtn');
   rightpanel = document.getElementById('section_droite');
   if(showpanel == 1)
   {
    console.log(showpanel+" cacher + droite=l100");
    showpanel=0;
	controlpanel.innerHTML="	<span id='controltxt'>Légende</span><a id='controlbtn' class='open' href='#' onclick='SlidePanel();' title='Afficher la Légende'>&raquo;";
	controltxt.innerHTML="Légende";
	controlbtn.innerHTML='&raquo;';
	rightpanel.className="l100";
	contribuez.className="cacher";
	leftpanel.className="cacher";
    console.log(showpanel+" bouton="+ controlbtn+ " txt="+controltxt);
	document.getElementById("map").style.width = "100%";
   }
   else
   {
    console.log(showpanel+" afficher + droite=l75");
	showpanel=1;
	controlpanel.innerHTML="	<span id='controltxt'> </span><a id='controlbtn' class='open' href='#' onclick='SlidePanel();' title='Agrandir la Carte'>&laquo;&laquo;";
	controltxt.innerHTML=" ";
	controlbtn.innerHTML='&laquo;&laquo;';
	rightpanel.className="l75";
	leftpanel.className="l25";
	contribuez.className="cacher";
    console.log(showpanel+" bouton="+ controlbtn+ " txt="+controltxt);
	document.getElementById("map").style.width = "100%";
   }
   map.updateSize();
}

function icone(feature,nb) {
	img="img/wifi_bleu.png";
	// img pour operateurs ile-sans-fil,zap,etc.
	wifi_operator=getAttrib(feature.cluster[nb].attributes,"internet_access:operator").toLowerCase();
	wifi_operator=wifi_operator.replace(/î/gi, "i");
	wifi_operator=wifi_operator.replace(/é/gi, "e");
	wifi_operator=wifi_operator.replace(/-/g, " ");
	if (wifi_operator == "ile sans fil")
		{img="img/wifi_ilesansfil.png";}
    else if (wifi_operator.indexOf("zap") ==0) {
		img="img/wifi_zap.png"; 
	}
// console.log(wifi_operator+' img='+img)
return img;
}
var style_wifi_couche = new OpenLayers.Style({
	pointRadius: "${radius}",
	label:" ${nombre}",
	fontColor: "${couleur}",
	fontSize: "${police}",
	fillColor: "#0033FF",
	fillOpacity: "${fillOpacity}",
	strokeColor: "#330099",
	strokeWidth: "${strokeWidth}",
	strokeOpacity: 1,
	externalGraphic: "${externalGraphic}",
	graphicWidth: "${graphicWidth}"

	
	/*
	graphicXOffset: 2,
	graphicYOffset: -2
	graphicXOffset: "${graphicXOffset}",
	graphicYOffset: "${graphicYOffset}"
	
function layerContext()
{
    theXOffset    = 10;
    theYOffset    = 10;
    theResolution = map.getResolution();
 
    var context = {
        getXO : function(){
            return theXOffset * theResolution / map.getResolution();
        },
        getYO : function(){
            return theYOffset * theResolution / map.getResolution();
        }
    };
	
externalgraphiq attrib elem=count
externalgraphiq feature elem=layer
externalgraphiq feature elem=lonlat
externalgraphiq feature elem=data
externalgraphiq feature elem=id
externalgraphiq feature elem=geometry
externalgraphiq feature elem=state
externalgraphiq feature elem=attributes
externalgraphiq feature elem=style
externalgraphiq feature elem=cluster
externalgraphiq feature elem=renderIntent
externalgraphiq feature elem=fid
externalgraphiq feature elem=bounds
externalgraphiq feature elem=url
externalgraphiq feature elem=modified
externalgraphiq feature elem=initialize
externalgraphiq feature elem=destroy
externalgraphiq feature elem=clone
externalgraphiq feature elem=onScreen
externalgraphiq feature elem=getVisibility
externalgraphiq feature elem=createMarker
externalgraphiq feature elem=destroyMarker
externalgraphiq feature elem=createPopup
externalgraphiq feature elem=atPoint
externalgraphiq feature elem=destroyPopup
externalgraphiq feature elem=move
externalgraphiq feature elem=toState
externalgraphiq feature elem=CLASS_NAME
externalgraphiq feature elem=marker
externalgraphiq feature elem=popupClass
externalgraphiq feature elem=popup	
	*/
},
{
	context: {
		externalGraphic:  function(feature) {
			zoom=map.getZoom();
			console.log("img zoom="+zoom+" zoom_nb="+zoom_nb);
			if (feature.attributes.count==1 &&zoom>=zoom_nb) {
			for (elem in feature.attributes)
				console.log("externalgraphiq attrib elem="+elem);
			//for (elem in feature) console.log("externalgraphiq feature elem="+elem);
			console.log("externalgraphiq attributes ="+feature.attributes);
			console.log("externalgraphiq layer ="+feature.layer);
			console.log("externalgraphiq lonlat ="+feature.lonlat);
			console.log("externalgraphiq data ="+feature.data);
			console.log("externalgraphiq geometry ="+feature.geometry);

			img=icone(feature,0);
			//if (zoom>zoom_icone_plus && img=="img/wifi_bleu.png") {img="img/wifi_bleu_48.png";}
			}
			else {img="";}
			// console.log(wifi_operator+' img='+img)
			return img;
		},
		graphicWidth: function(feature) {
			zoom=map.getZoom();
			//echelle=map.getScale()
			console.log("Dimension graphique zoom="+zoom+" zoom_icone_plus="+zoom_icone_plus);
			if (zoom>zoom_icone_plus) {return 48;}
			else {return 24;} 
		},
		graphicXOffset: function(feature) {
			/*zoom=map.getZoom();
			console.log("x zoom="+zoom);
			if (zoom>zoom_icone_plus) {return 7.0;}
			else {return 0.0;} */
		var XOffset = 7;
		var YOffset = -20;
		var Resolution = map.getResolution();
			XOffset= XOffset * Resolution / map.getResolution();
			console.log("y zoom="+zoom+" XOffset="+XOffset);
            return XOffset
		},
		graphicYOffset: function(feature) {
			/*zoom=map.getZoom();
			console.log("y zoom="+zoom);
			if (!zoom) {zoom=1;}
			if (zoom>zoom_icone_plus) {return -20.0;}
			else {return 0.0;} */
			var XOffset = 7;
			var YOffset = -20;
			var Resolution = map.getResolution();
			YOffset= YOffset * Resolution / map.getResolution();
			console.log("y zoom="+zoom+" YOffset="+YOffset);
            return YOffset
		},
		fillOpacity:  function(feature) {
			zoom=map.getZoom();
			if (feature.attributes.count==1 &&zoom>=zoom_nb) {
				//console.log("fill zoom="+zoom);
				if (zoom>zoom_icone_plus) {return 0.8;}
				else {return 1;}
			}
			else {return 0.3;}
		},
		radius: function(feature) {
			zoom=map.getZoom();
			nb=Math.floor(feature.attributes.count*2/5);
			rayon=Math.min(nb, 12 )+10;
			if (zoom<zoom_nb) {rayon=rayon/1.2}
			else if (zoom<zoom_icone_plus) {rayon=rayon/1.1}
			return rayon;
		},
		strokeWidth: function(feature) {
			zoom=map.getZoom();
			if (zoom<zoom_nb) {return 1;}
			else {return 2;}
		},	
		nombre: function(feature) {
			zoom=map.getZoom();
			var nb=feature.attributes.count;
			//blanc=String.fromCharCode("\\u00A0");
			blanc=".";
			if (nb==1 || zoom<zoom_nb) {return blanc;}
			else {return nb;}
		},	
		couleur: function(feature) {
			zoom=map.getZoom();
			var nb=feature.attributes.count;
			if (nb==1 || zoom<zoom_nb) {return "blue";}
			else {return "navy";}
		},	
		police: function(feature) {
			zoom=map.getZoom();
			var nb=feature.attributes.count;
			if (nb==1 || zoom<zoom_nb) {return "1px";}
			else {return "1em";}
		}	
	}
});

/*

69	        new OpenLayers.Rule({
70	            title: "All",
71	            minScaleDenominator: 3000000,
72	            symbolizer: {
73	                graphicName: "star",
74	                pointRadius: 5,
75	                fillColor: "#99ccff",
76	                strokeColor: "#666666",
77	                strokeWidth: 1
78	            }
79	        })
80	    ];

new OpenLayers.Rule({
    filter: new OpenLayers.Filter.Logical({
        filters: [
        type: OpenLayers.Filter.Comparison.GREATER_THAN,
        property: "count",
        value: 50

		new OpenLayers.Filter.Comparison({
                type: OpenLayers.Filter.Comparison.LESS_THAN_OR_EQUAL_TO,
                property: "zoom",
                value: map.zoom
            })
        ],
        type: OpenLayers.Filter.Logical.AND
    }),
    symbolizer: {
        externalGraphic: "/path/to/images/1.png",
        graphicHeight: 25,
        graphicWidth: 25,
        graphicOpacity: 1.0
    }
}));

new OpenLayers.Rule({
    filter: new OpenLayers.Filter.Logical({
        filters: [
            new OpenLayers.Filter.Comparison({
                type: OpenLayers.Filter.Comparison.EQUAL_TO,
                property: "type",
                value: "type_1"
            }),
            new OpenLayers.Filter.Comparison({
                type: OpenLayers.Filter.Comparison.LESS_THAN_OR_EQUAL_TO,
                property: "zoom",
                value: map.zoom
            })
        ],
        type: OpenLayers.Filter.Logical.AND
    }),
    symbolizer: {
        externalGraphic: "/path/to/images/1.png",
        graphicHeight: 25,
        graphicWidth: 25,
        graphicOpacity: 1.0
    }
}));

        type: OpenLayers.Filter.Comparison.BETWEEN,
        property: "count",
        lowerBoundary: 2,
        upperBoundary: 14
    }),
        type: OpenLayers.Filter.Comparison.GREATER_THAN,
        property: "count",
        value: 50
    }),
    	
var rule = new OpenLayers.Rule({
  filter: new OpenLayers.Filter.Comparison({
    type: '==',
    property: 'baz.dolor', // this does not work! 
    value: 'sit'
  }),
  symbolizer: {
    graphic: true,
    graphicZIndex: 100,
    backgroundGraphicZIndex: 500,
    externalGraphic: OpenLayers.Util.getImagesLocation() + 'foo.png',
    graphicHeight: 22,
    graphicWidth: 22,
    graphicTitle: '${display_name}',
    strokeColor: '#FF0000'
  }
});
*/

var styleMap_wifi_couche = new OpenLayers.StyleMap({
	"default": style_wifi_couche,
	"select": {
		fillColor: "#8aeeef",
		strokeColor: "#32a8a9",
		fillOpacity: 0.6
	}
});

var texte,operator,building,amenity,shop,leisure,tourism,cuisine,adresse, 
	 addr_no, addr_address,addr_city,addr_suburb,addr_postcode,tel,
	 wifi_operator,wifi_ssid;

var selectedfeature, 
    popup, 
    field;
		
//++++++++++++++++++++++++++++++++++++++++++++++++++++++
	
/*	========================================
 Exemples, outils dynamiques OSM
 https://github.com/neogeomat/webdri/blob/master/index.php
 http://gis.stackexchange.com/questions/53850/how-to-change-color-of-feature-to-mark-it-as-recently-modified-with-openlayers
 evt http://openlayers.org/dev/examples/dynamic-text-layer.html
    ========================================*/


	
function getAttrib(variable,cle) {
	if (variable.hasOwnProperty(cle)) {
		valeur=variable[cle];
	}
	else {
	valeur="";
		}
	return valeur;
}
	
function onLoadEnd  () {
    console.log("Points Wifi, Téléchargement terminé");
	InfoDemarre.deactivate();
	InfoDemarre.destroy();
}	

function featureHighlighted(evt) {
	var feature = evt.feature;
	nb_objets=feature.cluster.length
	if (nb_objets>1) {
		texte = "<div>"+nb_objets+" Points Wifi</div>";
	}
	else {
		name=getAttrib(feature.cluster[0].attributes,"name");
		texte=name;
		}
	console.log("highlight texte="+texte);
    hover = new OpenLayers.Popup.FramedCloud("Popup",
        new OpenLayers.LonLat(5.6, 50.6),
        null,
        "<div>"+texte+"</div>",
        null,
        false);
    //map.addPopup(hover);
}

function featureunhighlighted(evt) {
	hover.hide();
}

// Nécessaire pour interaction seulement, et non pas pour affichage.
function onPopupClose(evt) {
	// 'this' is the popup.
	var feature = this.feature;
	if (feature.layer) { // Cet objet n'est pas détruit
		selector.unselect(feature);
	} else { // After "moveend" or "refresh" events on POIs layer all 
			 //     features have been destroyed by the Strategy.BBOX
		this.destroy();
	}
}

function onFeatureUnselect(evt) {
	var feature = evt.feature;
	if (feature.popup) {
		map.removePopup(feature.popup);
		feature.popup.destroy();
		feature.popup = null;
	}
}

function onFeatureSelect(event) {

	feature=event.feature;
    selectedfeature = feature;

	attribs="Attribs";
	for (var cl in feature.cluster) {
		for (var cle in feature.cluster[cl].attributes) {
		  attribs+="<br/>"+ cle + "="+feature.cluster[cl].attributes[cle];
		}
	}
	nb_objets=feature.cluster.length
	if (nb_objets>1) {
		texte = "<div>"+nb_objets+" Points Wifi - Zoomez davantage pour localiser ces Points Wifi.</div><div class=\"lieu\">";
		titre = +nb_objets+" Points Wifi - Zoomez davantage pour localiser ces Points Wifi.";
	}
	else {texte="";titre="Points Wifi";}
	
	for (var nb = 0; nb < nb_objets; nb++) {
	
		//for (var item in feature.cluster[nb].style) {console.log(item+ " "+feature.cluster[nb].style.item);}
		img=icone(feature,nb);
		//console.log("==> " +wifi_operator+' img='+img)		
		//console.log("gr "+feature.cluster[nb].style.externalGraphic);
		name=getAttrib(feature.cluster[nb].attributes,"name");
		highway=getAttrib(feature.cluster[nb].attributes,"highway");
		operator=getAttrib(feature.cluster[nb].attributes,"operator");
		amenity=getAttrib(feature.cluster[nb].attributes,"amenity");
		building=getAttrib(feature.cluster[nb].attributes,"building");
		shop=getAttrib(feature.cluster[nb].attributes,"shop");
		leisure=getAttrib(feature.cluster[nb].attributes,"leisure");
		tourism=getAttrib(feature.cluster[nb].attributes,"tourism");
		cuisine=getAttrib(feature.cluster[nb].attributes,"cuisine");
		tel=getAttrib(feature.cluster[nb].attributes,"phone");
		wifi_operator=getAttrib(feature.cluster[nb].attributes,"internet_access:operator");
		wifi_ssid=getAttrib(feature.cluster[nb].attributes,"internet_access:ssid");
		addr_no=getAttrib(feature.cluster[nb].attributes,"addr:housenumber");
		addr_street=getAttrib(feature.cluster[nb].attributes,"addr:street");
		addr_city=getAttrib(feature.cluster[nb].attributes,"addr:city");
		addr_suburb=getAttrib(feature.cluster[nb].attributes,"addr:suburb");
		addr_postcode=getAttrib(feature.cluster[nb].attributes,"addr:postcode");

		if (name=="") {
			if (operator!="") {
				name=operator;
			}
			else {name="Lieu non identifié";}
		}

		if (amenity!="") {
			if (amenity.toLowerCase()=="library") {amenity="Bibliothèque";}
			else if (amenity.toLowerCase()=="townhall") {amenity="Hotel de ville";}
			else if (amenity.toLowerCase()=="community_centre") {amenity="Centre communautaire";}
			else if (amenity.toLowerCase()=="social_centre") {amenity="Centre de services sociaux";}
			else if (amenity.toLowerCase()=="cafe") {amenity="Café";}
			else if (amenity.toLowerCase()=="place_of_worship") {amenity="Lieux de culte";}
			else if (amenity.toLowerCase()=="hospital") {amenity="Hopital";}
			else if (amenity.toLowerCase()=="pharmacy") {amenity="Pharmacie";}
			else if (amenity.toLowerCase()=="clinic") {amenity="Clinique médicale";}
			else if (amenity.toLowerCase()=="doctors") {amenity="Médecins";}
			else if (amenity.toLowerCase()=="post_office") {amenity="Poste";}
			else if (amenity.toLowerCase()=="marina") {amenity="Marina";}
			else if (amenity.toLowerCase()=="fast_food") {amenity="Resto Rapide";}
			else if (amenity.toLowerCase()=="ice_cream") {amenity="Crème Glacée";}
			}
		if (shop!="") {
			if (shop.toLowerCase()=="supermarket") {shop="Supermarché";}
			else if (shop.toLowerCase()=="mall") {shop="Centre commercial";}
			else if (shop.toLowerCase()=="department_store") {shop="Magasin";}
			else if (shop.toLowerCase()=="clothes") {shop="Vêtements";}
			else if (shop.toLowerCase()=="stationery") {shop="Articles de bureau";}
			else if (shop.toLowerCase()=="bakery") {shop="Boulangerie";}
		}
		if (leisure!="") {
			if (leisure.toLowerCase()=="park") {leisure="Parc";}
			else if (leisure.toLowerCase()=="sports_centre") {leisure="Centre sportif";}
			else if (leisure.toLowerCase()=="marina") {leisure="Marina";}
			else if (leisure.toLowerCase()=="cinema") {leisure="Cinéma";}
		}
		if (tourism!="") {
			if (tourism.toLowerCase()=="camp_site") {tourism="Camping";}
			else if (tourism.toLowerCase()=="hotel") {tourism="Hotel";}
			else if (tourism.toLowerCase()=="hostel") {tourism="Auberge";}
			else if (tourism.toLowerCase()=="guest_house") {tourism="Chambre d'hôte";}
		}
		if (highway!="") {
			if (highway.toLowerCase()=="rest_area") {highway="Halte routière";}
		}
		if (building!="") {
			if (building.toLowerCase()=="hospital") {building="Hopital";}
			else if (building.toLowerCase()=="school") {building="École";}
			else if (building.toLowerCase()=="public_building") {amenity="Immeuble public";}
		}

		if (cuisine!="") {
			// trad
		}
		if (tel!="") {
			if (tel.indexOf("+")<0) {tel="+"+tel;}

	}

	type_osm="";
	if (amenity!="") {
		type_osm=amenity
		}
		else if (shop!="") {
			type_osm=shop;
		}
		else if (leisure!="") {
			type_osm=leisure;
		}
		else if (tourism!="") {
			type_osm=tourism;
		}
		else if (highway!="") {
			type_osm=highway;
		}
		else if (building!="yes") {
			type_osm=building;
		}

	adresse="";
	if (addr_no != "") {adresse+=addr_no +", ";}
	if (addr_street != "") {adresse+=addr_street;}
	if (addr_suburb != "") {adresse+="<br/>"+addr_suburb;}
	if (addr_city != "") {adresse+="<br/>"+addr_city;}
	if (addr_postcode != "") {adresse+=" "+addr_postcode;}
	if (tel != "") {adresse+="<br/>"+tel;}

	wifi="";
	if (wifi_operator != "") {wifi+="<em>Opérateur Wifi</em>: "+wifi_operator+"<br/>";}
	if (wifi_ssid != "") {wifi+="SSID: "+wifi_ssid;}
	if (wifi!="") {wifi="<div>"+wifi+"</div>";}

	if (nb_objets>5) {
		if (nb<16) {texte += "<h3 class=\"popup2\">"+name+" &nbsp; <em>"+type_osm+"</em></h3>";}
		else if (nb==16) {texte+="<h3 class=\"popup\"> ... </h3>"} 
	}
	else {
		texte += "<div class=\"lieu\">\n<img class=\"gauche\" src=\""+img+"\"/> <h3 class=\"popup\">"+name+"<br/><br/></h3><div class=\"sautgauche\">"+type_osm+"</div>";
		texte+="<div>"+adresse+"</div><div class=\"wifi\">"+wifi+"</div>\n</div>\n";
	}
		// console.log("texte "+texte);
	}
	
	if (nb_objets>1) {
		texte +="</div>\n";
		titre = nb_objets+" Points Wifi - Zoomez davantage pour localiser ces Points Wifi.";
	}
	// console.log("texte "+texte);
	
    popup = new OpenLayers.Popup.FramedCloud(
        "wifiPopup",
        feature.geometry.getBounds().getCenterLonLat(),
        new OpenLayers.Size(largeur_popup,hauteur_popup),
        texte,
        null,
        true,onPopupClose
    );
	feature.popup = popup;
	popup.feature = feature;
	popup.autoSize = false;
	map.addPopup(popup, true);
	
}

function couche_wifi (couche,desc,url_couche,affichage) {
	// url - Service Overpass, requête dynamique, base OSM
    var url_wifi_couche = url;
	console.log("fonction couche_wifi desc="+desc+", url_couche="+url_couche);
    couche = new OpenLayers.Layer.Vector(desc, {

	strategies: [new OpenLayers.Strategy.Fixed(), new OpenLayers.Strategy.Cluster() /*,new OpenLayers.Strategy.BBOX({resFactor: 1.1})*/ ],
        styleMap: styleMap_wifi_couche,
        protocol: new OpenLayers.Protocol.HTTP({
            url: url_couche,  // url_region["bbox"],   //<-- url absolu ou relatif fichier .osm
            format: new OpenLayers.Format.OSMMeta()
        }),
		forceFixedZoomLevel: true, numZoomLevels: null, restrictedMinZoom: 10, MinZoom: 10, MaxZoom: 10,
        projection: new OpenLayers.Projection("EPSG:4326")
    });
	couche.visibility= true;
    map.addLayer(couche);
	console.log("fonction couche_wifi - couche wifi ajoutée");
    //controls      
    selector = new OpenLayers.Control.SelectFeature(couche);
    map.addControl(selector);
    selector.activate();

	couche.events.on({
            "featureselected": onFeatureSelect,
            "featureunselected": onFeatureUnselect,
			"loadend": onLoadEnd
        });

	var highlightCtrl = new OpenLayers.Control.SelectFeature(couche, {
			hover: true,
			highlightOnly: true,
			renderIntent: "temporary",
			eventListeners: {
				featurehighlighted: featureHighlighted,
				featureunhighlighted: featureunhighlighted
			}
		});

}

//++++++++++++++++++++++++++++++++++++++++++++++++++++++


function init_carte(){
    var options = {
		projection: new OpenLayers.Projection("EPSG:900913"),
		displayProjection: new OpenLayers.Projection("EPSG:4326"),
		controls: [
				new OpenLayers.Control.Navigation(),
				new OpenLayers.Control.KeyboardDefaults(),
				new OpenLayers.Control.PanZoomBar({forceFixedZoomLevel: true, zoomWorldIcon: false}),
				new OpenLayers.Control.TouchNavigation({
					dragPanOptions: {
						enableKinetic: true
					}
				}),
				new OpenLayers.Control.ScaleLine(),
				new OpenLayers.Control.MousePosition(),
				new OpenLayers.Control.KeyboardDefaults(),
				new OpenLayers.Control.LayerSwitcher(), 
				new OpenLayers.Control.Attribution({
				div: document.getElementById('map_attribution') }),
				new OpenLayers.Control.Scale()
		]
	};
	
	OpenLayers.Lang.setCode("fr");
	OpenLayers.ProxyHost = "proxy-overpass.php?url=";

	map = new OpenLayers.Map("map",options);	  
	map.numZoomLevels = null;

	// calque de base
    layerMapnik = new OpenLayers.Layer.OSM.Mapnik("OpenStreetMap",
		{attribution: "&copy; contributeurs <a href='http://www.openstreetmap.org/copyright'>OpenStreetMap</a>",
	zoomOffset:10,
	minZoomLevel: 10,
	resolutions: [152.87405654907226, 76.4370282714844,38.2185141357422,19.1092570678711,9.55462853393555,4.77731426696777,2.38865713348389,1.19432856674194]
	});file:///C:/Users/Pierre/Downloads/Montreal%20Wi-Fi%20_09_12_11.csv
    map.addLayer(layerMapnik);
	InfoDemarre=new OpenLayers.Control.LoadingPanel();
	map.addControl(InfoDemarre); 
/*
    var lonLat = new OpenLayers.LonLat(lon, lat)
        .transform(new OpenLayers.Projection("EPSG:4326"), new OpenLayers.Projection("EPSG:900913"));

    map.setCenter (lonLat, zoom);
            // Variant 2: Zoom to show all            
            //map.zoomToMaxExtent();

            // Variant 3: Using a bounding box: Most but not all of the world
            //map.zoomToExtent(
            //    new OpenLayers.Bounds(-150.0,68.0,150.0,-50.0).transform(
            //        map.displayProjection,  
            //        map.projection));	document.getElementById('message'.innerHTML="Points Wifi : Chargement des données ...");
    var bounds = new OpenLayers.Bounds();
	bounds=map.getExtent();
	// https://github.com/digitalfotografen/Openlayers---Sparklines/blob/master/examples/sweden.html
	var WGS84 = new OpenLayers.Projection("EPSG:4326");
	bounds.transform(map.getProjectionObject(), WGS84);
	console.log("bounds "+bounds+", bbox="+bounds.toBBOX());
    //bounds.toBBOX(); // returns 4,5,5,6
	//       maxExtent: new OpenLayers.Bounds(-20037508, -20037508, 20037508, 20037508.34)
    //bounds.left, .bottom, .right, .top
// qc 61549
	console.log("ajout couche montreal "+url_region["montreal"]);

	couche_wifi (couche=wifi_region,
		desc="Points Wifi", url=url_region["quebec"], affichage=true);
	console.log("couche_wifi ajoutée");
*/
	// départ - selection wifi pour sherbrooke
	//selection_wifi_lonlat(lon=-72.1527,lat=45.3965,zoom=10);
	// départ - selection wifi pour quebec
	selection_wifi_lonlat(lon=-71.1598,lat=46.8226,zoom=10);

// suivre événements http://dev.openlayers.org/releases/OpenLayers-2.13.1/examples/events.html
	function handleZoom(event) {
		var map = event.object;
		resolution=map.getResolution();
		echelle=map.getScale();
		minzoom=map.getMinZoom();
		console.log("movestart zoom="+map.getZoom()+" resolution="+resolution+" echelle="+echelle+" minzoom="+minzoom)
		if (map.getZoom() < 11) {
			OpenLayers.Event.stop(event);
	 }
	}
	map.events.register('movestart', map, handleZoom)

    }


	// ************ NOMINATIM change your country code for language localisation
	//  var lang="ar" , "en" or "fr" modified by html language selection button;
       var lang="fr";
	
	
   
	/*========================================================================
	  NOMINATIM SEARCH functions
	  source of functions http://wiki.openstreetmap.org/wiki/User:SunCobalt/OpenLayers_Suche
	  ========================================================================
	*/
	
    function jumptolonlat(lon,lat){
	/* lieu sélectionné via recherche Nominatim
	   - déplace carte au lieu sélectionné, zoom=11 (pourra éventuellement varier)
	   - charge données wifi via Overpass
	*/
		var LonLat = new OpenLayers.LonLat(lon,lat).transform(new OpenLayers.Projection("EPSG:4326"),map.getProjectionObject());
		//map.setCenter(LonLat,11); 
		document.getElementById("result").className="cacher";
		selection_wifi_lonlat(lon,lat,11);
		console.log("jumptolonlat terminé");
       return false;
      }
	  
    function fragmapquest(){

// ************ change your country code for language localisation
	//  var lang="ar" , "en" or "fr" modified by html language selection button;
	var urlnominatim="http://nominatim.openstreetmap.org/search.php";
	var urlmapquest="http://pierzen.dev.openstreetmap.org/hot/openlayers/nominatim/mapquestjs.php";
	   search_query=document.getElementById("nominatim_query").value;
	   exclude_place_ids="state,region,administrative";
       url=urlmapquest+"?q="+search_query+"&limit=8"+"&lang="+lang;
	   console.log("Nominatim, url="+url);
       var http = new XMLHttpRequest();
       http.open("GET",url,false);    
       http.send(null);
       nominatim_line=http.responseText.split("\n");
       resultdiv = document.getElementById("result");
	   resultdiv.className="displayblock";
	   i18n_info_enter_locality_above="Spécifiez une localité ci-dessus, et appuyez sur le bouton Rechercher.";
	   i18n_info_no_search_results_for="Aucun résultat pour";
	   i18n_info_search_results_for="Résultats de recherche pour";
	   result_close="<a id='result_close' class='right_panel' href='#' onclick=\"document.getElementById('result').className='cacher';\" title='Fermer le panneau des Résultats'><button class='btn small'><img src='img/close.png' class='right_panel' /></button></a><br />";
	   resultdiv.innerHTML=result_close+resultdiv.innerHTML;		 
	   
	   if (search_query.length==0) {
	    msg_search_results=i18n_info_enter_locality_above;
		 resultdiv.innerHTML=result_close+msg_search_results;		 
		}
	   else  {
		msg_search_results=i18n_info_no_search_results_for+" \""+search_query+"\"<br /><ul>";
       if(nominatim_line.length<=3){
        resultdiv.innerHTML="<br />"+msg_search_results+" \""+search_query+"\"";
		resultdiv.innerHTML=result_close+resultdiv.innerHTML;		 
       }else{
	    search_results_for=i18n_info_search_results_for;
        resultdiv.innerHTML=search_results_for +" \""+search_query+"\"";
        i=0;
        for(i=0;i<nominatim_line.length;i++){
         nominatim_col=nominatim_line[i].split("\t");
		 for (col in nominatim_col) {
			console.log("Nominatim col "+col + ", " +nominatim_col[col]);
		}
         if((nominatim_col[0]*nominatim_col[0]>0)||(nominatim_col[1]*nominatim_col[1]>0)){
          if(i==0){selection_wifi_lonlat(nominatim_col[0],nominatim_col[1]),11;}
          displaytext=nominatim_col[2];
          resultdiv.innerHTML=resultdiv.innerHTML+"<font size=2><li><a href=# onmouseup=\"selection_wifi_lonlat("+nominatim_col[0]+","+nominatim_col[1]+",11);\" onclick='//vectorLayer.visibility=false;'>"+displaytext+"</a></li><br>"; 
          }
         }
         resultdiv.innerHTML=resultdiv.innerHTML+"</ul>";
		 resultdiv.innerHTML=result_close+resultdiv.innerHTML;		 

        }
		}
        return false;
       } 
	   
	/*========================================================================
	  NOMINATIM SEARCH functions end
	  ========================================================================*/

	
//]]>	  
</script>
<style type="text/css">

  /*  Style général */

	body {
		 margin:auto;
		 max-width:1024px;
		 border: 2px solid navy;
	 }
	#wifi {
		 clear:both;
	 }
	 .l25{
        width : 24.9%;
	 }
	 .l75{
        width : 74.9%;
	 }
 .l90{
	width:90%;
	margin:0.5em;
 }
 .l100{
	width:99%;
 }
 .cacher{
 	display:none;
 }
 #section_gauche {
		margin-top:2em;
		float: left;
		font-size:85%;
    }
    #section_droite, #menu_region {
		float: right;
		clear: right;
    }
    #menu_region {
		margin-right:2em;
    }
	#legende {
        width : 40%;
		float: left;
		background-color:#EEEAEE;
	}
	#legende img {
		/* margin:0.7em; float : left; */
		height:22px;
	}
	#naviguer {
	/*
		float: right;
         max-width : 56%;
		 */
		margin-top:3em;
		margin-right:0.5em;
		clear:right;
	}
	#bouton_contribuez {
		margin:2.5em 1em 1em;
	}
	#baspage {
		 clear:both;padding-top:1em;
		 border-top: 2px solid navy;
		 margin:0.2em;
		 padding-top:0.2em;
		 background:#ddd;
		 text-align:center;
	 }
	.logos {
		text-align:center;height:60px;
	}
	.gauche {
		float: left;
    }
	.sautgauche {
			clear: left;
    }	.bleu {
		color:navy; font-size:1.1em;
	}
	hr.popup {
		color:#0099CC;
		background-color:#0099CC;
	}
	h1 {
		font-size:1.2em;text-align:center;color:purple;
		line-height:1.3em;margin:0;padding:0;
	}
	#search_panel form h1 {
		display:inline;color:white;
		font-family: cursive, fantasy, verdana;
		font-style:italic;font-size:1.25em;margin:0.2em;
		}
	#search_panel form img {
		display:inline;
		}
	#contribuez {
		margin: 0.4 em;
		padding 0.4 em;
	}
	h2 {
		font-size:1.2em;
	}
	.lieu {
		border: 2px solid #3399FF;
		border-radius: 0.6em;
		margin:0.1em;padding:0.2em;
		font-size:0.9em;
		font-weight:normal;
	}
	h3.popup {
		clear:right;
		color:navy;
		background-color:#ccddcc;
		margin-top:0.3em;
		margin-bottom:0.3em;
		font-size:1.05em;
	}
	h3.popup2 {
		 font-size:0.8em;
	 }
	h4.popup {
		background-color:#DADEDE;
	}
	.dimcarte {
		 width:100%;height:600px;
	}
	.gras {font-weight:bold;}
	.centre {text-align:center;}

	.cadre
	{
	 border: 1px solid #888;
	 padding: 0.5em;
	  background-color: #ffffcc;
	 font-size: 10pt;
	 box-shadow: inset 2px 2px 2px 2px #888;
	 border-top-left-radius: 8px;
	 border-top-right-radius: 8px;
	 border-bottom-right-radius: 8px;
	 border-bottom-left-radius: 8px;
	}

	.olControlLoadingPanel {
		background-image:url(img/loading.gif);
		position: relative;
		width: 110%;
		height:110%;
		background-position:center;
		background-repeat:no-repeat;
		display: none; 
	}
	
/*  Style pour petits écrans */
@media screen and (max-width:640px) {
	#contribuez, #legende {
            width : 95%;
            max-width : 95%;
			float: none;
	}
	#naviguer {
            width : 95%;
            max-width : 95%;
			float: left;
	}
	#section_gauche
	{
	  font-size: 95%;
	}
	h1 {
		font-size:1.05em;
	}
	.lieu {
		font-size:0.95em;
	}
	h3.popup {
		clear:right;
		color:navy;
		background-color:#ccddcc;
		margin-top:0.3em;
		margin-bottom:0.3em;
		font-size:1.05em;
	}
	h3.popup2 {
		 font-size:1em;
	 }
	.ligne {
		 display:inline;
	 }
	.dimcarte {
		 width:100%;height:400px;
	}
}		
	
/* style pour panneau glissant */
#controls {
	clear:both;
	border-top : 0.3em #039 solid;
	background-color:#039;
	color:white;
	padding-top:0.2em;
  }
#search_panel {
	float : right; 
	margin-right: 3em;
	position:relative;
/*	width:65%;
	height:1.7em; */
	text-align:left;
	font-size:85%;
  }
#search_panel img, #search_panel form, #search_panel fieldset {
	display:inline;
  }
#result {
	border: 2px #039 solid;
	background-color:#fff8c6;
	color:#039;
	padding-bottom:0.3em;
  }
#control_panel {
	float : left;
/*	width:30%; */
	position:relative;
	text-align:right;
  }
#control_panel, #control_panel a{
	display:inline;
  }
#control_panel:hover , #control_panel a:hover {
	background-color:#eeeaed;
}
a#controlbtn{
	float: left;
	background-color:#EAE9EA;
	color:#039;
	font-weight:bold;
	font-size:1.2em;
	padding:0.3em;
	text-decoration: none;
}
#controltxt{
	font-size:small;
}
// afficher Contribuez
$('.bloc_contribuez').click(afficher_contribuez());
/*$('.bloc_contribuez').click(function(){
	document.getElementById("contribuez").className="l100";
});*/
 .btnsearch{
  background:#fef url(img/search.jpg) no-repeat left top;margin-left:0.2em;padding:0.3em;text-decoration:none;
}

.btn.error{background-color:#c43c35;background-repeat:repeat-x;background-image:-moz-linear-gradient(top, #ee5f5b, #c43c35);background-image:-ms-linear-gradient(top, #ee5f5b, #c43c35);background-image:-webkit-gradient(linear, left top, left bottom, color-stop(0%, #ee5f5b), color-stop(100%, #c43c35));background-image:-webkit-linear-gradient(top, #ee5f5b, #c43c35);background-image:-o-linear-gradient(top, #ee5f5b, #c43c35);background-image:linear-gradient(top, #ee5f5b, #c43c35);filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#ee5f5b', endColorstr='#c43c35', GradientType=0);text-shadow:0 -1px 0 rgba(0, 0, 0, 0.25);border-color:#c43c35 #c43c35 #882a25;border-color:rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.25);}
.btn.success{background-color:#57a957;background-repeat:repeat-x;background-image:-khtml-gradient(linear, left top, left bottom, from(#62c462), to(#57a957));background-image:-moz-linear-gradient(top, #62c462, #57a957);background-image:-ms-linear-gradient(top, #62c462, #57a957);background-image:-webkit-gradient(linear, left top, left bottom, color-stop(0%, #62c462), color-stop(100%, #57a957));background-image:-webkit-linear-gradient(top, #62c462, #57a957);background-image:-o-linear-gradient(top, #62c462, #57a957);background-image:linear-gradient(top, #62c462, #57a957);filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#62c462', endColorstr='#57a957', GradientType=0);text-shadow:0 -1px 0 rgba(0, 0, 0, 0.25);border-color:#57a957 #57a957 #3d773d;border-color:rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.25);}
.btn:hover,.btn.primary{background-color:#339bb9;background-repeat:repeat-x;background-image:-khtml-gradient(linear, left top, left bottom, from(#5bc0de), to(#339bb9));background-image:-moz-linear-gradient(top, #5bc0de, #339bb9);background-image:-ms-linear-gradient(top, #5bc0de, #339bb9);background-image:-webkit-gradient(linear, left top, left bottom, color-stop(0%, #5bc0de), color-stop(100%, #339bb9));background-image:-webkit-linear-gradient(top, #5bc0de, #339bb9);background-image:-o-linear-gradient(top, #5bc0de, #339bb9);background-image:linear-gradient(top, #5bc0de, #339bb9);filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#5bc0de', endColorstr='#339bb9', GradientType=0);text-shadow:0 -1px 0 rgba(0, 0, 0, 0.25);border-color:#339bb9 #339bb9 #22697d;border-color:rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.25);}
.btn.info{text-align:left;color:#ffffff;background-color:#0064cd;background-repeat:repeat-x;background-image:-khtml-gradient(linear, left top, left bottom, from(#049cdb), to(#0064cd));background-image:-moz-linear-gradient(top, #049cdb, #0064cd);background-image:-ms-linear-gradient(top, #049cdb, #0064cd);background-image:-webkit-gradient(linear, left top, left bottom, color-stop(0%, #049cdb), color-stop(100%, #0064cd));background-image:-webkit-linear-gradient(top, #049cdb, #0064cd);background-image:-o-linear-gradient(top, #049cdb, #0064cd);background-image:linear-gradient(top, #049cdb, #0064cd);filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#049cdb', endColorstr='#0064cd', GradientType=0);text-shadow:0 -1px 0 rgba(0, 0, 0, 0.25);border-color:#0064cd #0064cd #003f81;border-color:rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.25);}
.btn{box-shadow:2px 2px 3px dimgray inset;
cursor:pointer;display:inline-block;background-color:#e6e6e6;background-repeat:no-repeat;background-image:-webkit-gradient(linear, 0 0, 0 100%, from(#ffffff), color-stop(25%, #ffffff), to(#e6e6e6));background-image:-webkit-linear-gradient(#ffffff, #ffffff 25%, #e6e6e6);background-image:-moz-linear-gradient(top, #ffffff, #ffffff 25%, #e6e6e6);background-image:-ms-linear-gradient(#ffffff, #ffffff 25%, #e6e6e6);background-image:-o-linear-gradient(#ffffff, #ffffff 25%, #e6e6e6);background-image:linear-gradient(#ffffff, #ffffff 25%, #e6e6e6);padding:5px 14px 6px;text-shadow:0 1px 1px rgba(255, 255, 255, 0.75);color:#333;font-size:13px;line-height:normal;border:1px solid #ccc;border-bottom-color:#bbb;-webkit-border-radius:4px;-moz-border-radius:4px;border-radius:4px;-webkit-box-shadow:inset 0 1px 0 rgba(255, 255, 255, 0.2),0 1px 2px rgba(0, 0, 0, 0.05);-moz-box-shadow:inset 0 1px 0 rgba(255, 255, 255, 0.2),0 1px 2px rgba(0, 0, 0, 0.05);box-shadow:inset 0 1px 0 rgba(255, 255, 255, 0.2),0 1px 2px rgba(0, 0, 0, 0.05);-webkit-transition:0.1s linear all;-moz-transition:0.1s linear all;-ms-transition:0.1s linear all;-o-transition:0.1s linear all;transition:0.1s linear all;}.btn{background-position:0 -15px;color:#333;text-decoration:none;}
.btn:focus{outline:1px dotted #666;}
.btn.active,.btn:active{-webkit-box-shadow:inset 0 2px 4px rgba(0, 0, 0, 0.25),0 1px 2px rgba(0, 0, 0, 0.05);-moz-box-shadow:inset 0 2px 4px rgba(0, 0, 0, 0.25),0 1px 2px rgba(0, 0, 0, 0.05);box-shadow:inset 0 2px 4px rgba(0, 0, 0, 0.25),0 1px 2px rgba(0, 0, 0, 0.05);}
.btn.disabled{cursor:default;background-image:none;filter:progid:DXImageTransform.Microsoft.gradient(enabled = false);filter:alpha(opacity=65);-khtml-opacity:0.65;-moz-opacity:0.65;opacity:0.65;-webkit-box-shadow:none;-moz-box-shadow:none;box-shadow:none;}
.btn[disabled]{cursor:default;background-image:none;filter:progid:DXImageTransform.Microsoft.gradient(enabled = false);filter:alpha(opacity=65);-khtml-opacity:0.65;-moz-opacity:0.65;opacity:0.65;-webkit-box-shadow:none;-moz-box-shadow:none;box-shadow:none;}
.btn.large{font-size:15px;line-height:normal;padding:9px 14px 9px;-webkit-border-radius:6px;-moz-border-radius:6px;border-radius:6px;}
.btn.small{padding:3px 5px 3px;font-size:12px;}
button.btn::-moz-focus-inner,input[type=submit].btn::-moz-focus-inner{padding:0;border:0;}
div#nominatim	{
	padding-left: 0.1em; padding-right: 0.1em;
	 overflow: hidden; background-color:#FFF8C6;
	 font-size:90%;
}
// http://www.francoischarron.com/les-zones-dacces-gratuit-a-internet-wifi-au-quebec/-/DuVQ8PjjnF/
// http://trac.osgeo.org/openlayers/wiki/SettingZoomLevels
/*
TMS Layers
Setting the resolutions array in the traditional manner does not work with TMS layers by design. Instead of removing values from the resolutions array to restrict zoom levels (for unrendered TMS layers or other reasons), include both a serverResolutions array and a resolutions array. The serverResolutions array should include all resolutions available (whether rendered or not) on the server, while the resolutions array should have all resolutions that are presented to the user - therefore the resolutions array should be a subset of serverResolutions. For instance,
resolutions: [0.17578125, 0.087890625, 0.0439453125],
serverResolutions: [0.703125, 0.3515625, 0.17578125, 0.087890625, 0.0439453125],

*/
</style> 
<link href="http://openlayers.org/api/2.13/theme/default/style.css" type="text/css" rel="stylesheet"></head>
<link rel="stylesheet" href="http://openlayers.org/api/2.13/theme/default/style.mobile.css" type="text/css">
<body onload="init_carte()">
<div id="wifi">
	<div id="section_droite" class="l100">
		<div id="controls">
			<div id="control_panel"><a id="controlbtn" class="open" href="#" onclick="SlidePanel();" title="Agrandir la Carte">&raquo; <span id="controltxt"> Légende</span> </a> &nbsp; </div>
			<div id="search_panel">
			<form action="" method="get" onsubmit="fragmapquest();return false;">
				<a href="https://github.com/pierzen/zoneswifi/"><h1>Zones Wifi</h1></a>
				&nbsp;<!--img src="img/wifi-vert-droite.png" title="wifi" /-->&nbsp;:&nbsp;
				<input vki_attached="true" id="nominatim_query" class="keyboardInput" title="Exemples :&#10; &#10;Grande bibliotheque, Montréal&#10;475, boulevard De Maisonneuve Est, Montréal&#10;Café République, Montréal" placeholder="Ville, province ..." onfocus="if(this.value == 'Localité au Mali ...') { this.value = ''; }" name="q" size="36" lang="fr" type="text"> <!-- <img title="Display virtual keyboard interface" class="keyboardInputInitiator" alt="Display virtual keyboard interface" src="http://pierzen.dev.openstreetmap.org/hot/openlayers/js/greywyvern-keyboard.png"> --> &nbsp;
				<button class="btn small" type="submit"><img src="img/nominatim-search.png" alt="" value="point" style="height:16px;"> Rechercher</button> &nbsp; 
			 </form>
			</div>
			<div classs="clearboth"><br><br></div>
		</div>
		<div id="message"></div>
		<div id="result" class="cacher"></div>
		<div id="map" class="smallmap olMap dimcarte"></div>
	</div>
	<div id="section_gauche" class="cacher">
		<div id="naviguer">
		<p>Les icônes des <em class="bleu">Points Wifi</em>, sur la carte du Sans Fil, permettent de localiser les zones d'accès gratuit au Wifi et les divers réseaux Sans Fil.</p><p>Cliquez sur ces icônes pour afficher la description des lieux.</p>
	<p> Recherchez une ville et les donnnées seront extraites pour la zone affichée à l'écran. De là, il est possible de zoomer et voir le détail sur les différents points Wifi.
	</p>
		</div>
	<div id="bouton_contribuez">
	<h2><a href="#" class="btn small info" onclick="afficher_contribuez()">Données&nbsp;OSM</a></h2>
	</div>
	<div id="contribuez" class="cacher">
		<h1>Contribuez à OpenStreetMap et mettez à jour les infos sur les points Wifi gratuits</h1>
		<p>Inscrivez-vous comme contributeur <a href="http://www.openstreetmap.org/?q=montreal+quebec#map=11/45.5626/-73.6791&layers=H">OpenStreetMap</a> pour ajouter ou mettre à jour les points de service.
		</p>
		<p>Les attributs suivants permettre de décrire les points wifi :</p>
		<ul>
		<li> <em>internet_access=wlan</em> : Point wifi</li>
		<li><em>internet_access:operator = (nom) ou (adresse internet) </em> indique à quel réseau est associé ce point</li>
		<li><em>internet_access:ssid = Identification du réseau</em> dans liste des points wifi</li>
		</ul>
	</div>
		
		<div style="clear:both;"></div>
	</div>
<!--	<div id="baspage">
	Données extraites de la base de données OpenStreetMap via le <a href="http://overpass-turbo.eu/">Serveur de requêtes Overpass</a>.
	&nbsp; &copy; <a href="https://github.com/pierzen/zoneswifi/">Pierre Béland, 2013</a>
	</div>
-->
</div>
</body>
</html>
