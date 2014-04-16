var json = {
        "map": {
            "title": "Barraques de pedra seca",
            "size": "1024",
            "geographic": false,
            "servers": [{
                "name": "Ortofotos ICC",
                "url": "http://sagitari.icc.cat/tilecache/tilecache.py?FORMAT=image%2Fjpeg&EXCEPTIONS=application%2Fvnd.ogc.se_xml&SRS=EPSG%3A23031&SERVICE=WMS&VERSION=1.1.1&REQUEST=GetMap&STYLES=&LAYERS=topo&BBOX=393973.5,4562820,399423.5,4568080&WIDTH=256&HEIGHT=256"
            },
            {
                "name": "Límits administratius",
                "url": "http://wikipedra.catpaisatge.net/cgi-bin/mapserv?map=/usr/share/wikipedra/maps/referencia.map&FORMAT=image%2Fpng&TRANSPARENT=true&EXCEPTIONS=application%2Fvnd.ogc.se_xml&INFOFORMAT=application%2Fvnd.ogc.gml&SERVICE=WMS&VERSION=1.1.1&REQUEST=GetMap&STYLES=&LAYERS=municipis&SRS=EPSG%3A23031&BBOX=393973.5,4562820,399423.5,4568080&WIDTH=545&HEIGHT=526",
                "layers": [{
                    "title": "Municipis",
                    "legend": "http://wikipedra.catpaisatge.net/cgi-bin/mapserv?map=/usr/share/wikipedra/maps/referencia.map&REQUEST=GetLegendGraphic&VERSION=1.1.1&LAYER=municipis&FORMAT=image/png&SERVICE=WMS"
                }]
            }],
            "printoptions": {
                "showcoordinates": false,
                "showreferencesystem": true,
                "legendwidth": "150",
                "personaltext": "WIKIPEDRA - Barraques de pedra seca",
                "qfactor": "2",
                "showselectionitem": true,
                "footerheight": "60",
                "fixedscales": "",
                "scalebar": "numeric",
                "legendqfactor": "0.7",
                "showreferencemap": false
            },
            "locale": {
                "title": "",
                "text1": "",
                "text2": "",
                "create_pdf": "",
                "coordinates": "Coordenades de la cantonada inferior esquerra del mapa",
                "referencesystem": "Sistema de referÃ¨ncia",
                "mapscale": "Escala del mapa",
                "printscale": "Escala d'impressiÃ³",
                "currentscale": "Actual (aproximada)",
                "numPage": "Pàgina",
                "numPageOf": "de"
            }
        }
    };