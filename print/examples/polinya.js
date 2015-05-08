var json={
    "map": {
        "title": "Polinyà: guia urbana",
        "size": 1024,
        "epsg": 23031,        
        "geographic": false,
        "servers": [{
            "name": "Ortofotos ICC",
            "type": "wms",
            "url": "http://shagrat.icc.es/lizardtech/iserv/ows?FORMAT=image%2Fjpeg&VERSION=1.1.1&EXCEPTIONS=application%2Fvnd.ogc.se_xml&SERVICE=WMS&REQUEST=GetMap&STYLES=&LAYERS=mtc10m&SRS=EPSG%3A23031&BBOX=428349.0942365,4600330.8094567,430981.69698155,4601709.2878789&WIDTH=1492&HEIGHT=781",
            "layers": [{
                "name": "topo",
                "title": "Topogràfic",
                "legend": ""
            }]
        },
        {
            "name": "Mapserver Polinyà",
            "type": "wms",
            "url": "http://oslo.geodata.es/db2p/polinya?FORMAT=image%2Fpng&TRANSPARENT=true&VERSION=1.1.1&SERVICE=WMS&REQUEST=GetMap&STYLES=&EXCEPTIONS=application%2Fvnd.ogc.se_inimage&LAYERS=cultural%2Ccorreus%2Ccabina%2Cadministratiu&SRS=EPSG%3A23031&BBOX=428349.0942365,4600330.8094567,430981.69698155,4601709.2878789&WIDTH=1492&HEIGHT=781",
            "layers": [{
                "title": "Administratiu"
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
            "showLogo": false,
            "boxGap": 3
        }
    }
};