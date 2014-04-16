var json={
    "map": {
        "title": "Ajuntament de Polinyà",
        "size": "1024",
        "scale": "5000",
        "geographic": false,
        "servers": [{
            "name": "Ortofotos ICC",
            "url": "http://shagrat.icc.es/lizardtech/iserv/ows?FORMAT=image%2Fjpeg&VERSION=1.1.1&EXCEPTIONS=application%2Fvnd.ogc.se_xml&SERVICE=WMS&REQUEST=GetMap&STYLES=&LAYERS=orto25c%2Corto25c&SRS=EPSG%3A23031&BBOX=428349.0942365,4600330.8094567,430981.69698155,4601709.2878789&WIDTH=1492&HEIGHT=781",
            "layers": [{
                "name": "topo",
                "title": "Ortofoto",
                "legend": ""
            }]
        },
        {
            "name": "Mapserver Polinyà",
            "url": "http://oslo.geodata.es/db2p/polinya?FORMAT=image%2Fpng&TRANSPARENT=true&VERSION=1.1.1&SERVICE=WMS&REQUEST=GetMap&STYLES=&EXCEPTIONS=application%2Fvnd.ogc.se_inimage&LAYERS=cultural%2Ccorreus%2Ccabina%2Cadministratiu&SRS=EPSG%3A23031&BBOX=428349.0942365,4600330.8094567,430981.69698155,4601709.2878789&WIDTH=1492&HEIGHT=781",
            "layers": [{
                "title": "Administratiu",
                "legend": "http://oslo.geodata.es/db2p/polinya?LAYER=administratiu&REQUEST=GetLegendGraphic&VERSION=1.1.1&FORMAT=image/png&SERVICE=WMS"
            },
            {
                "title": "Correus",
                "legend": "http://oslo.geodata.es/db2p/polinya?LAYER=correus&REQUEST=GetLegendGraphic&VERSION=1.1.1&FORMAT=image/png&SERVICE=WMS"
            },
            {
                "title": "Cultural",
                "legend": "http://oslo.geodata.es/db2p/polinya?LAYER=cultural&REQUEST=GetLegendGraphic&VERSION=1.1.1&FORMAT=image/png&SERVICE=WMS"
            },
            {
                "title": "Cabina",
                "legend": "http://oslo.geodata.es/db2p/polinya?LAYER=cabina&REQUEST=GetLegendGraphic&VERSION=1.1.1&FORMAT=image/png&SERVICE=WMS"
            }]
        }],
        "config": {
            "showNorth": true,
            "showEpsg": true,
            "showScale": true,
            "showLegend": true,
            "boxGap": 2,
            "directOutput": false
        },
        "locale": {
            "coordinates": "Coordenades de la cantonada inferior esquerra del mapa",
            "referenceSystem": "Sistema de referÃ¨ncia",
            "mapScale": "Escala del mapa",
            "numPage": "Pàgina",
            "numPageOf": "de"
        }
    }
};