// configure tabber
var tabberOptions = {
    manualStartup:true, 
    'onClick': function(argsObj)
        { 
            FleetBattles.loadTab(argsObj); 
        }, 
    'addLinkId': true 
}

// initialize "namespace"
var FleetBattles = new Object();

// loads the content of a tab if there isn't any
FleetBattles.loadTab = function(tabberArgs)
{ 
    var tabTitle = tabberArgs.tabber.tabs[tabberArgs.index].headingText.split(" ").join("")
    var tab = document.getElementById(tabTitle);
    
    // check for additional parameters
    var additionalArguments = new Array();
    
    var startTime = $("#timestampStart").val();
    if(startTime !== null)
    {
        additionalArguments.push("starttime="+startTime);
    }
    
    var endTime = $("#timestampEnd").val();
    
    if(endTime !== null)
    {
        additionalArguments.push("endtime="+endTime);
    }
    
    
    // tab empty?
    if(tab !== null && tab.innerHTML === "")
    {
        tab.innerHTML = "<div style=\"width: 100%; text-align: center\"><img src=\""+fleetBattlesLoadingImage+"\" /></div>";
        var ajaxRequestUrl = document.getElementById("ajaxRequestUrl").value;
        if(ajaxRequestUrl.toString().indexOf("?") == -1)
        {
            ajaxRequestUrl += "?"+tabTitle+"="+tabTitle;
        }
        
        else
        {
            ajaxRequestUrl += "&"+tabTitle+"="+tabTitle;
        }
        
        if(additionalArguments.length > 0)
        {
           ajaxRequestUrl +=  "&"+additionalArguments.join("&");
        }
        
        jQuery.get(ajaxRequestUrl, function(html){
            tab.innerHTML = html;
        });
    }
    
}


var alliedEntities = Array();
var hostileEntities = Array();

FleetBattles.switchSideForEntity = function(entity)
{
    // switch from a to e
    if(entity.getSide() == "a")
    {
        entity.setSide("e");
        // add entity to other side
        this.addEntityToSide(entity, "e");
        
        // delete entity from allied side
        var i;
        for(i = 0; i < alliedEntities.length; i++)
        {
            if(alliedEntities[i] == entity)
            {
                alliedEntities.splice(i, 1);
            }
        }
        
        // delete entity from allied table
        var alliedTable = document.getElementById("alliedTable");
        var html = getChildById(alliedTable, entity.type+"-"+entity.id);
        html.parentNode.removeChild(html); 
    }
    
    // switch from e to a
    else
    {
        entity.setSide("a");
        // add entity to other side
        this.addEntityToSide(entity, "a");
        
        // delete entity from allied side
        var i;
        for(i = 0; i < hostileEntities.length; i++)
        {
            if(hostileEntities[i] == entity)
            {
                hostileEntities.splice(i, 1);
            }
        }
        
        // delete entity from allied table
        var hostileTable = document.getElementById("hostileTable");
        var html = getChildById(hostileTable, entity.type+"-"+entity.id);
        html.parentNode.removeChild(html); 
    }
}


FleetBattles.addEntityToSide = function(entity, side)
{
    var i = null;
    // array holding entities of one side
    var entities = null;
    // HTML table with representation of entites of one side
    var entityTable;
    if(side == "a")
    {
        entities = alliedEntities;    
        entityTable = document.getElementById("alliedTable");
    }
    
    else
    {
        entities = hostileEntities;
        entityTable = document.getElementById("hostileTable");
    }
    
    
    var entityUpdated = false;
    // check if entity already is on allied side
    for(i = 0; i < entities.length; i++)
    {
        // found the same entity alread on the target side
        if(entities[i].getType() == entity.getType() && entities[i].getName() == entity.getName())
        {
            // update number of pilots for entity
            entities[i].setNumberOfPilots(entities[i].getNumberOfPilots()+entity.getNumberOfPilots());

            // update html representation
            var html = getChildById(entityTable, entity.getType()+"-"+entity.getId());
            html.parentNode.replaceChild(entities[i].toHtml(), html); 

            entityUpdated = true;
        }
    }

    // entity not found on allied side
    if(!entityUpdated)
    {
        // add to allied entities
        entities.push(entity);
        
        // add html
        entityTable.appendChild(entity.toHtml());
    }
}



// helper functions
var getChildById = function(parentElement, id)
{
    if(parentElement instanceof HTMLElement)
    {
        var childNodes = parentElement.childNodes;
        var i;
        for(i = 0; i < childNodes.length; i++)
        {
            if(childNodes[i].id != undefined && childNodes[i].id == id)
            {
                return childNodes[i];
            }
        }
    }
    
    return null;
}

