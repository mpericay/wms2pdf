var json2={
    "map": {
        "title": "Polinyà: urbanisme",
        "size": 1500,
        "scale": 5000,
        "epsg": 23031,        
        "geographic": false,
        "servers": [
            {
                "name": "Topo ICC",
            "url": "http://shagrat.icc.es/lizardtech/iserv/ows?FORMAT=image%2Fjpeg&VERSION=1.1.1&EXCEPTIONS=application%2Fvnd.ogc.se_xml&SERVICE=WMS&REQUEST=GetMap&STYLES=&LAYERS=mtc10m&SRS=EPSG%3A23031&BBOX=428349.0942365,4600330.8094567,430981.69698155,4601709.2878789&WIDTH=1492&HEIGHT=781",
            "layers": [{
                "name": "topo",
                "title": "Topogràfic"
                }]
            },
        {
            "name": "Mapserver Polinyà planejament",
            "url": "http://oslo.geodata.es/wms52/polinya/servidor/planejament_pol?FORMAT=image%2Fpng&TRANSPARENT=true&VERSION=1.1.1&SERVICE=WMS&REQUEST=GetMap&STYLES=&EXCEPTIONS=application%2Fvnd.ogc.se_inimage&LAYERS=qualificacions,sectors&SRS=EPSG%3A23031&BBOX=423984.79450989,4598473.8764316,434515.20549011,4603776.1235684&WIDTH=1492&HEIGHT=751",
            "opacity": 0.5,
            "layers": [{
                "title": "Qualificacions",
                "legend": "http://oslo.geodata.es/wms52/polinya/servidor/planejament_pol?REQUEST=GetLegendGraphic&VERSION=1.1.1&LAYER=qualificacions&FORMAT=image/png&SERVICE=WMS"
            },{
                "title": "Règim del sòl",
                "legend": "http://oslo.geodata.es/wms52/polinya/servidor/planejament_pol?REQUEST=GetLegendGraphic&VERSION=1.1.1&LAYER=sectors&FORMAT=image/png&SERVICE=WMS"
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