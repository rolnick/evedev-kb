Entity = function(id, type, name, imageUrl, infoUrl, numberOfPilots, side)
{
    this.id = id;
    this.type = type;
    this.name = name;
    this.imageUrl = imageUrl;
    this.side = side;
    this.numberOfPilots = numberOfPilots;
    this.infoUrl = infoUrl;
}


Entity.prototype.getId = function() 
{
    return this.id;
}

Entity.prototype.getType = function() 
{
    return this.type;
}

Entity.prototype.getName = function() 
{
    return this.name;
}

Entity.prototype.getImageUrl = function() 
{
    return this.imageUrl;
}

Entity.prototype.getNumberOfPilots = function() 
{
    return this.numberOfPilots;
}

Entity.prototype.setSide = function(side)
{
    this.side = side;
}

Entity.prototype.getSide = function()
{
    return this.side;
}

Entity.prototype.setNumberOfPilots = function(numberOfPilots)
{
    this.numberOfPilots = numberOfPilots;
}

// return the HTML element (= table row)
Entity.prototype.toHtml = function()
{
    // table row for entity
    var entityRow = document.createElement("tr");
    entityRow.id = this.type+"-"+this.id;
    entityRow.className = "kb-table-row-even";
    
    // logo column
    var entityLogoColumn = document.createElement("td");
    entityLogoColumn.width = 32;
    entityLogoColumn.height = 32;
    entityLogoColumn.className = "kb-table-cell";
   
    // logo img
    var entityLogo = document.createElement("img");
    entityLogo.width = 32;
    entityLogo.height = 32
    entityLogo.src = this.imageUrl;
    
    entityLogoColumn.appendChild(entityLogo);
    entityRow.appendChild(entityLogoColumn);
    
    var entityInfoColumn = document.createElement("td");
    entityInfoColumn.className = "kb-table-cell";
    
    var entityInfoLink = document.createElement("a");
    entityInfoLink.href = this.infoUrl;
    entityInfoLink.target = "_blank";
    if(this.type == "corp")
    {
        entityInfoLink.innerHTML = "(Corp) "+this.name;
    }
    
    else
    {
        entityInfoLink.innerHTML = this.name;
    }
    
    // add 
    entityInfoColumn.appendChild(entityInfoLink);
    entityInfoColumn.innerHTML += "<br/>Pilots: "+this.numberOfPilots;
    
    entityRow.appendChild(entityInfoColumn);
    
    // side switch column
    var entitySideSwitchColumn = document.createElement("td");
    entitySideSwitchColumn.className = "kb-table-cell";
    entitySideSwitchColumn.style.width = "40px";
    
    var entitySideSwitchLink = document.createElement("a");
    entitySideSwitchLink.href = "javascript:void(null)";
    entitySideSwitchLink.onclick = function(entity)
        { return function()
            {
                FleetBattles.switchSideForEntity(entity);
            }
        }(this);

    entitySideSwitchLink.innerHTML = "switch";
    entitySideSwitchColumn.appendChild(entitySideSwitchLink);
    
    var entitySideField = document.createElement("input");
    entitySideField.type = "hidden";
    entitySideField.name = "side_"+this.type+"-"+this.id;
    entitySideField.id = "side_"+this.type+"-"+this.id;
    entitySideField.value = this.side;
    entitySideSwitchColumn.appendChild(entitySideField);
    
    entityRow.appendChild(entitySideSwitchColumn);
    
    return entityRow;
    
}