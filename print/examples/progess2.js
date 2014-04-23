var json={
    "map": {
        "title": "EII Santa Coloma",
        "size": "1024",
        "epsg": 23031,        
        "geographic": false,
        "servers": [{
            "name": "Ortofotos ICC",
            "url": "http://shagrat.icc.es/lizardtech/iserv/ows?FORMAT=image%2Fjpeg&EXCEPTIONS=application%2Fvnd.ogc.se_xml&SRS=EPSG%3A23031&SERVICE=WMS&VERSION=1.1.1&REQUEST=GetMap&STYLES=&LAYERS=mtc5m&BBOX=433733.6463,4588371.9086,434991.9009,4589542.6564&WIDTH=720&HEIGHT=720",
            "layers": [{
                "name": "topo",
                "title": "Ortofoto",
                "legend": ""
            }]
        },
        {
            "name": "Geoserver Progess",
            "url": "http://si.progess.com:8008/geoserver/wms?LAYERS=barris,comunitats_obertes,mediacions_obertes,dinamitzacions_obertes&FORMAT=image%2Fgif&TRANSPARENT=true&SERVICE=WMS&VERSION=1.1.1&REQUEST=GetMap&STYLES=&SRS=EPSG%3A23031&BBOX=433733.6463,4588371.9086,434991.9009,4589542.6564&WIDTH=720&HEIGHT=720",
            "layers": [{
                "title": "Barris",
                "legend": "http://si.progess.com:8008/geoserver/wms?LAYER=barris&REQUEST=GetLegendGraphic&VERSION=1.1.1&FORMAT=image/png&SERVICE=WMS"
            },
            {
                "title": "Comunitats obertes",
                "legend": "http://si.progess.com:8008/geoserver/wms?LAYER=comunitats_obertes&REQUEST=GetLegendGraphic&VERSION=1.1.1&FORMAT=image/png&SERVICE=WMS"
            },
            {
                "title": "Mediacions obertes",
                "legend": "http://si.progess.com:8008/geoserver/wms?LAYER=mediacions_obertes&REQUEST=GetLegendGraphic&VERSION=1.1.1&FORMAT=image/png&SERVICE=WMS"
            },
            {
                "title": "Dinamitzacions obertes",
                "legend": "http://si.progess.com:8008/geoserver/wms?LAYER=dinamitzacions_obertes&REQUEST=GetLegendGraphic&VERSION=1.1.1&FORMAT=image/png&SERVICE=WMS"
            }]
        }]
    }
};