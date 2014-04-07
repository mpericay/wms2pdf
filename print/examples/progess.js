var json = {
        "map": {
            "title": "EII Santa Coloma",
            "css": "",
            "referenceimage": "",
            "maxsize": "1024",
            "legendstyle": "embedded",
            "servers": [{
                "name": "Ortofotos ICC",
                "url": "http://mapcache.icc.cat/map/bases_noutm/service?FORMAT=image%2Fjpeg&EXCEPTIONS=application%2Fvnd.ogc.se_xml&SRS=EPSG%3A4326&SERVICE=WMS&VERSION=1.1.1&REQUEST=GetMap&STYLES=&LAYERS=topo&BBOX=2.1746243945313,41.435603918945,2.2340156054688,41.467139075195&WIDTH=256&HEIGHT=256",
                "layers": [{
                    "name": "topo",
                    "title": "Ortofoto",
                    "legend": ""
                }]
            },
            {
                "name": "Mediacions",
                "url": "http://si.progess.com:8008/geoserver/wms?LAYERS=mediacions&FORMAT=image%2Fgif&TRANSPARENT=true&SERVICE=WMS&VERSION=1.1.1&REQUEST=GetMap&STYLES=&SRS=EPSG%3A4326&BBOX=2.1746243945313,41.435603918945,2.2340156054688,41.467139075195&WIDTH=1356&HEIGHT=720",
                "layers": [{
                    "name": "mediacions",
                    "title": "Mediacions",
                    "legend": "http://si.progess.com:8008/geoserver/wms?LAYER=mediacions&REQUEST=GetLegendGraphic&VERSION=1.1.1&FORMAT=image/png&SERVICE=WMS"
                }]
            },
            {
                "name": "Mediacions obertes",
                "url": "http://si.progess.com:8008/geoserver/wms?LAYERS=mediacions_obertes&FORMAT=image%2Fgif&TRANSPARENT=true&SERVICE=WMS&VERSION=1.1.1&REQUEST=GetMap&STYLES=&SRS=EPSG%3A4326&BBOX=2.1746243945313,41.435603918945,2.2340156054688,41.467139075195&WIDTH=1356&HEIGHT=720",
                "layers": [{
                    "name": "mediacions_obertes",
                    "title": "Mediacions obertes",
                    "legend": "http://si.progess.com:8008/geoserver/wms?LAYER=mediacions_obertes&REQUEST=GetLegendGraphic&VERSION=1.1.1&FORMAT=image/png&SERVICE=WMS"
                }]
            }],
            "printoptions": {
                "showcoordinates": false,
                "showreferencesystem": true,
                "legendwidth": "150",
                "personaltext": "Ajuntament de Santa Coloma de Gramenet",
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