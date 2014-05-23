var json3 = {
        "map": {
            "title": "Barraques de pedra seca",
            "size": "1024",
            "epsg": 23031,            
            "geographic": false,
            "servers": [{
                "name": "Ortofotos ICC",
                "url": "http://sagitari.icc.cat/tilecache/tilecache.py?FORMAT=image%2Fjpeg&EXCEPTIONS=application%2Fvnd.ogc.se_xml&SRS=EPSG%3A23031&SERVICE=WMS&VERSION=1.1.1&REQUEST=GetMap&STYLES=&LAYERS=topo&BBOX=393973.5,4562820,399423.5,4568080&WIDTH=256&HEIGHT=256"
            },
            {
                "name": "Límits administratius",
                "url": "http://wikipedra.catpaisatge.net/cgi-bin/mapserv?map=/usr/share/wikipedra/maps/referencia.map&FORMAT=image%2Fpng&TRANSPARENT=true&EXCEPTIONS=application%2Fvnd.ogc.se_xml&INFOFORMAT=application%2Fvnd.ogc.gml&SERVICE=WMS&VERSION=1.1.1&REQUEST=GetMap&STYLES=&LAYERS=municipis,unitats_paisatgistiques&SRS=EPSG%3A23031&BBOX=393973.5,4562820,399423.5,4568080&WIDTH=545&HEIGHT=526",
                "layers": [{
                    "title": "Municipis",
                    "legend": "http://wikipedra.catpaisatge.net/cgi-bin/mapserv?map=/usr/share/wikipedra/maps/referencia.map&REQUEST=GetLegendGraphic&VERSION=1.1.1&LAYER=municipis&FORMAT=image/png&SERVICE=WMS"
                },{
                    "title": "Unitats paisatgístiques",
                    "legend": "http://www.aquestallegenda.com/es/completament/inventada.png"
                }]
            }],
            "config": {
                "showNorth": true,
                "showEpsg": false,
                "showScale": true,
                "showLegend": true,
                "showLogo": false,
                "ignoreLegendErrors": true,
                "boxGap": 5
            }
        }
    };