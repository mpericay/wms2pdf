var json2={
    "map": {
        "title": "Polinyà: urbanisme",
        "size": 1500,
        "scale": 5000,
        "epsg": 23031,        
        "geographic": false,
        "servers": [
        {
            "name": "Mapserver Polinyà planejament",
            "url": "http://oslo.geodata.es/wms52/polinya/servidor/planejament_pol?FORMAT=image%2Fpng&TRANSPARENT=true&VERSION=1.1.1&SERVICE=WMS&REQUEST=GetMap&STYLES=&EXCEPTIONS=application%2Fvnd.ogc.se_inimage&LAYERS=qualificacions,sectors&SRS=EPSG%3A23031&BBOX=423984.79450989,4598473.8764316,434515.20549011,4603776.1235684&WIDTH=1492&HEIGHT=751",
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