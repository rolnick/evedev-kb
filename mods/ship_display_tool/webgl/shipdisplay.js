        // Declare ship globally so you can test functionality from your javascript console
        var ship = null;
        if (hull=='') {
            throw new Error('no Hull specified');
        }
        function onDocumentLoad()
        {
            var canvas = document.getElementById('mainCanvas');
            var isMobile = false; //initiate as false
            // device detection
            if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|ipad|iris|kindle|Android|Silk|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(navigator.userAgent) 
    || /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(navigator.userAgent.substr(0,4))) isMobile = true
            ccpwgl.initialize(canvas, {'postprocessing' : true});
            ccpwgl.setResourceUnloadPolicy(0);
            var scene = ccpwgl.loadScene('res:/dx9/scene/universe/' + nebula + '.red');
            var camera = new TestCamera(canvas);
            camera.minDistance = 1;
            camera.maxDistance = 20000;
            camera.rotationX = -0.4;
            camera.rotationY = 0.3;
            camera.fov = 30;
            camera.distance = 2000;
            camera.nearPlane = 1;
            camera.farPlane = 10000000;
            camera.minPitch = -0.5;
            camera.maxPitch = 0.65;
            camera.rightMouseMove = true;
            ccpwgl.setCamera(camera);
            var distanceScaler = 4.5;
            // Variable to hold project load states
            var hasProjectLoaded = false;
            var preRendered = false;
            var overlaysAdded = false;
            // Turn Scene Rendering and Updating off so the user doesn't see the scene and ships loading
            ccpwgl.enableRendering(false);
            ccpwgl.enableUpdate(false);
            //Phoenis Turrets transforms are bugged so remove them
            //if (hull == 'cdn1_t1') {turrets = [];}

        /**
         * Creates boosters for a ship loaded from .red files, and sets them up as per the supplied sof.race object
         * This function needs to be run once a ship has loaded
         * @param ship - A ship loaded via .red files (and not via the space object factory)
         * @param race - A Space Object Factory race object (ie. sof.race.gallente)
         */
        function createSofBoostersForNonSofShip(ship, race)
        {
            if (!ship.isLoaded() || !race) return;

            function _get(obj, property, defaultValue) {
                if (property in obj) {
                    return obj[property];
                }
                return defaultValue;
            }

            function _assignIfExists(dest, src, attr) {
                if (attr in src) {
                    dest[attr] = src[attr];
                }
            }

            function SetupBoosters(wrappedObject, hull, race)
            {
                var booster = new ccpwgl_int.EveBoosterSet();
                var hullBooster = hull['booster'];
                var raceBooster = _get(race, 'booster', {});
                _assignIfExists(booster, raceBooster, 'glowScale');
                _assignIfExists(booster, raceBooster, 'glowColor');
                _assignIfExists(booster, raceBooster, 'warpGlowColor');
                _assignIfExists(booster, raceBooster, 'symHaloScale');
                _assignIfExists(booster, raceBooster, 'haloScaleX');
                _assignIfExists(booster, raceBooster, 'haloScaleY');
                _assignIfExists(booster, raceBooster, 'haloColor');
                _assignIfExists(booster, raceBooster, 'warpHaloColor');

                booster.effect = new ccpwgl_int.Tw2Effect();
                booster.effect.effectFilePath = 'res:/Graphics/Effect/Managed/Space/Booster/BoosterVolumetric.fx';
                booster.effect.parameters['NoiseFunction0'] = new ccpwgl_int.Tw2FloatParameter('NoiseFunction0', _get(raceBooster.shape0, 'noiseFunction', 0));
                booster.effect.parameters['NoiseSpeed0'] = new ccpwgl_int.Tw2FloatParameter('NoiseSpeed0', _get(raceBooster.shape0, 'noiseSpeed', 0));
                booster.effect.parameters['NoiseAmplitudeStart0'] = new ccpwgl_int.Tw2Vector4Parameter('NoiseAmplitudeStart0', _get(raceBooster.shape0, 'noiseAmplitureStart', [0, 0, 0, 0]));
                booster.effect.parameters['NoiseAmplitudeEnd0'] = new ccpwgl_int.Tw2Vector4Parameter('NoiseAmplitudeEnd0', _get(raceBooster.shape0, 'noiseAmplitureEnd', [0, 0, 0, 0]));
                booster.effect.parameters['NoiseFrequency0'] = new ccpwgl_int.Tw2Vector4Parameter('NoiseFrequency0', _get(raceBooster.shape0, 'noiseFrequency', [0, 0, 0, 0]));
                booster.effect.parameters['Color0'] = new ccpwgl_int.Tw2Vector4Parameter('Color0', _get(raceBooster.shape0, 'color', [0, 0, 0, 0]));
        
                booster.effect.parameters['NoiseFunction1'] = new ccpwgl_int.Tw2FloatParameter('NoiseFunction1', _get(raceBooster.shape1, 'noiseFunction', 0));
                booster.effect.parameters['NoiseSpeed1'] = new ccpwgl_int.Tw2FloatParameter('NoiseSpeed1', _get(raceBooster.shape1, 'noiseSpeed', 0));
                booster.effect.parameters['NoiseAmplitudeStart1'] = new ccpwgl_int.Tw2Vector4Parameter('NoiseAmplitudeStart1', _get(raceBooster.shape1, 'noiseAmplitureStart', [0, 0, 0, 0]));
                booster.effect.parameters['NoiseAmplitudeEnd1'] = new ccpwgl_int.Tw2Vector4Parameter('NoiseAmplitudeEnd1', _get(raceBooster.shape1, 'noiseAmplitureEnd', [0, 0, 0, 0]));
                booster.effect.parameters['NoiseFrequency1'] = new ccpwgl_int.Tw2Vector4Parameter('NoiseFrequency1', _get(raceBooster.shape1, 'noiseFrequency', [0, 0, 0, 0]));
                booster.effect.parameters['Color1'] = new ccpwgl_int.Tw2Vector4Parameter('Color1', _get(raceBooster.shape1, 'color', [0, 0, 0, 0]));

                booster.effect.parameters['WarpNoiseFunction0'] = new ccpwgl_int.Tw2FloatParameter('WarpNoiseFunction0', _get(raceBooster.warpShape0, 'noiseFunction', 0));
                booster.effect.parameters['WarpNoiseSpeed0'] = new ccpwgl_int.Tw2FloatParameter('WarpNoiseSpeed0', _get(raceBooster.warpShape0, 'noiseSpeed', 0));
                booster.effect.parameters['WarpNoiseAmplitudeStart0'] = new ccpwgl_int.Tw2Vector4Parameter('WarpNoiseAmplitudeStart0', _get(raceBooster.warpShape0, 'noiseAmplitureStart', [0, 0, 0, 0]));
                booster.effect.parameters['WarpNoiseAmplitudeEnd0'] = new ccpwgl_int.Tw2Vector4Parameter('WarpNoiseAmplitudeEnd0', _get(raceBooster.warpShape0, 'noiseAmplitureEnd', [0, 0, 0, 0]));
                booster.effect.parameters['WarpNoiseFrequency0'] = new ccpwgl_int.Tw2Vector4Parameter('WarpNoiseFrequency0', _get(raceBooster.warpShape0, 'noiseFrequency', [0, 0, 0, 0]));
                booster.effect.parameters['WarpColor0'] = new ccpwgl_int.Tw2Vector4Parameter('WarpColor0', _get(raceBooster.warpShape0, 'color', [0, 0, 0, 0]));

                booster.effect.parameters['WarpNoiseFunction1'] = new ccpwgl_int.Tw2FloatParameter('WarpNoiseFunction1', _get(raceBooster.warpShape1, 'noiseFunction', 0));
                booster.effect.parameters['WarpNoiseSpeed1'] = new ccpwgl_int.Tw2FloatParameter('WarpNoiseSpeed1', _get(raceBooster.warpShape1, 'noiseSpeed', 0));
                booster.effect.parameters['WarpNoiseAmplitudeStart1'] = new ccpwgl_int.Tw2Vector4Parameter('WarpNoiseAmplitudeStart1', _get(raceBooster.warpShape1, 'noiseAmplitureStart', [0, 0, 0, 0]));
                booster.effect.parameters['WarpNoiseAmplitudeEnd1'] = new ccpwgl_int.Tw2Vector4Parameter('WarpNoiseAmplitudeEnd1', _get(raceBooster.warpShape1, 'noiseAmplitureEnd', [0, 0, 0, 0]));
                booster.effect.parameters['WarpNoiseFrequency1'] = new ccpwgl_int.Tw2Vector4Parameter('WarpNoiseFrequency1', _get(raceBooster.warpShape1, 'noiseFrequency', [0, 0, 0, 0]));
                booster.effect.parameters['WarpColor1'] = new ccpwgl_int.Tw2Vector4Parameter('WarpColor1', _get(raceBooster.warpShape1, 'color', [0, 0, 0, 0]));

                booster.effect.parameters['ShapeAtlasSize'] = new ccpwgl_int.Tw2Vector4Parameter('ShapeAtlasSize', [_get(raceBooster, 'shapeAtlasHeight', 0), _get(raceBooster, 'shapeAtlasCount', 0), 0, 0]);
                booster.effect.parameters['BoosterScale'] = new ccpwgl_int.Tw2Vector4Parameter('BoosterScale', _get(raceBooster, 'scale', [1, 1, 1, 1]));

                booster.effect.parameters['ShapeMap'] = new ccpwgl_int.Tw2TextureParameter('ShapeMap', raceBooster.shapeAtlasResPath);
                booster.effect.parameters['GradientMap0'] = new ccpwgl_int.Tw2TextureParameter('GradientMap0', raceBooster.gradient0ResPath);
                booster.effect.parameters['GradientMap1'] = new ccpwgl_int.Tw2TextureParameter('GradientMap1', raceBooster.gradient1ResPath);
                booster.effect.parameters['NoiseMap'] = new ccpwgl_int.Tw2TextureParameter('ShapeMap', "res:/Texture/Global/noise32cube_volume.dds.0.png");

                booster.effect.Initialize();

                booster.glows = new ccpwgl_int.EveSpriteSet();
                booster.glows.effect = new ccpwgl_int.Tw2Effect();
                booster.glows.effect.effectFilePath = 'res:/Graphics/Effect/Managed/Space/Booster/BoosterGlow.fx';
                booster.glows.effect.parameters['DiffuseMap'] = new ccpwgl_int.Tw2TextureParameter('DiffuseMap', 'res:/Texture/Particle/whitesharp.dds.0.png');
                booster.glows.effect.Initialize();

                var items = _get(hullBooster, 'items', []);
                for (var i = 0; i < wrappedObject.locators.length; ++i) {
                    if (wrappedObject.locators[i].name.indexOf('locator_booster_') !== -1) {
                        wrappedObject.locators[i].atlasIndex0 = _get(items[i], 'atlasIndex0', 0);
                        wrappedObject.locators[i].atlasIndex1 = _get(items[i], 'atlasIndex1', 0);
                    }
                }
                booster.Initialize();
                wrappedObject.boosters = booster;
            }

            // Cycle through all the wrapped objects in a ship and build our boosters
            ship.wrappedObjects.forEach(function (item) {
                var fakeSofHull = {booster: {items: []}};
                for (var i = 0; i < item.locators.length; i++) {
                    if (item.locators[i].name.search("locator_booster") != -1) {
                        fakeSofHull.booster.items.push({
                            transform: item.locators[i].transform
                        })
                    }
                }
                SetupBoosters(item, fakeSofHull, race);
            });
        }

            function doFire() {
                setTurrets(2);
                next = 6000 + Math.random()*10000;
                setTimeout(changeState, next);
            }
            
            function changeState() {
                next = 8000 + Math.random()*8000;
                if (ship.turrets[1].state == 1) {
                    setTurrets(0);
                    setTimeout(doFire, next);
                } else {
                    next = 8000 + Math.random()*8000;
                    if (Math.random() > 0.3) {
                        setTurrets(2);
                    } else {
                        setTurrets(1);
                    }
                    setTimeout(changeState, next);
                }
            }
 
            function setTurrets(state)
            {
                if(!ship || !ship.wrappedObjects || ship.turrets.length < 1) {
                    return;
                }
                    x = -300 + Math.random()*600;
                    y = -400 + Math.random()*1200;
                    for (var i = 1; i < ship.turrets.length; i++) {
                        if (ship.turrets[i].path.indexOf("Salvage") != -1) {
                            ship.setTurretTargetPosition(i, [ x/20, y/20, 500]);
                        } else if (ship.turrets[i].path.indexOf("Tractor") != -1) {
                            ship.setTurretTargetPosition(i, [ x/15, y/10, 2000]);
                        } else {
                            ship.setTurretTargetPosition(i, [ x/20, y/20, 10000]);
                        }
                        ship.setTurretState(i, state);
                    }
                }

            function changeSiege() {
                nextsiege = 25000 + Math.random()*5000;
                if (ship.siegeState == 0) {
                   ship.setSiegeState(ccpwgl.ShipSiegeState.SIEGE);
                } else {
                   ship.setSiegeState(ccpwgl.ShipSiegeState.NORMAL);
                }
                setTimeout(changeSiege, nextsiege);
            }

            function effectLoop() {
                for (i = 0; i < ship.overlays.length; i++) {
                    if (ship.overlays[i].overlay.name.indexOf("Boosting") != -1 || ship.overlays[i].overlay.name.indexOf("Repair") != -1) {
                        ship.overlays[i].overlay.curveSet.PlayFrom(0);
                    }
                }
            }
            function t3modeChange() {
                rnd = Math.random()
                switch (ship.t3dMode) {
                    case 0: if (rnd < 0.5) {
                            ship.setT3dMode(1);
                        } else {
                            ship.setT3dMode(2);
                        }
                        break;
                    case 1: if (rnd < 0.5) {
                            ship.setT3dMode(0);
                        } else {
                            ship.setT3dMode(2);
                        }
                        break;
                    case 2: if (rnd < 0.5) {
                            ship.setT3dMode(1);
                        } else {
                            ship.setT3dMode(0);
                        }
                        break;
                    default: break;
                }
            }

            function areLoadsPending()
            {
                return (!scene || ccpwgl_int.resMan._pendingLoads > 0)
            }
            function animationsLoading()
            {
                return (!ship || !ship.wrappedObjects[0].animation.loaded)
            }

            if (subsys.length == 5) {
                //workaround for broken Legion Subsystem
                subsys.sort();
                ship = scene.loadShip(subsys);
                ship.dna = ('dummy:'+ skin +':'+ booster);
            } else if (hull.substring(0, 5) == "res:/") {
                ship = scene.loadObject(hull);
            } else {
                loadRes(hull +':'+ skin +':'+ booster);
                if (hull == 'cap1_t1') {
                    var distanceScaler = 15;
                }

            }

            function loadRes(res, callback, err)
            {
                /// Passes dna
                if (res.match(/(\w|\d|[-_])+:(\w|\d|[-_])+:(\w|\d|[-_])+/))
                {
                    function getConstructor(data) {
                        var hull = res.split(':')[0];
                        var h = data.hull[hull];
                        if (!h) {
                            err({err: true, msg: 'Could not find hull', value: hull});
                            return null;
                        }

                        var constructor = (h.buildClass == 2) ? scene.loadObject : scene.loadShip;
                        ship = constructor.call(scene, res, callback);
                    }

                    ccpwgl.getSofData(getConstructor);
                }
            };

            //scene.sunDirection = vec3.create([0.8, 0.1, 1.4]);
            sun = scene.loadSun('res:/fisfx/lensflare/white_tiny.red');
 
            function autoFocus(spaceObject, distanceScaler)
            {
                // The spaceObject must be loaded to get it's bounding sphere data
                if (!spaceObject.isLoaded())
                {
                    throw new ccpwgl.IsStillLoadingError();
                }
                var center = vec3.create([0,0,0]);
                if (spaceObject.wrappedObjects.length == 1) {
                // Get the radius of the space object
                var spaceObjectRadius = parseInt(spaceObject.getBoundingSphere()[1]);
                }
                else
                {
                var spaceObjectRadius = parseInt(spaceObject.getBoundingSphere()[1]) * 3;
                /*var radius = 0;
                for (i = 0; i < spaceObject.wrappedObjects.length; i++) {
                    outer = vec3.create();
                    vec3.scale(spaceObject.wrappedObjects[i].boundingSphereCenter, (vec3.length(spaceObject.wrappedObjects[i].boundingSphereCenter)+spaceObject.wrappedObjects[i].boundingSphereRadius)/vec3.length(spaceObject.wrappedObjects[i].boundingSphereCenter), outer);
                    vec3.add(center, outer);
                }
                vec3.scale(center, 1/(spaceObject.wrappedObjects.length));
                for (i = 0; i < spaceObject.wrappedObjects.length; i++) {
                    toCenter = vec3.create();
                    vec3.subtract(spaceObject.wrappedObjects[i].boundingSphereCenter, center, toCenter);
                    if (vec3.length(toCenter) + spaceObject.wrappedObjects[i].boundingSphereRadius > radius) {
                        radius = (vec3.length(toCenter) + spaceObject.wrappedObjects[i].boundingSphereRadius);
                    }
                }
                var spaceObjectRadius = parseInt(radius);*/
                }
                // Set the camera's point of interest as the space object's position in world space
                camera.poi[0] = spaceObject.getTransform()[12]+center[0]/2-(spaceObjectRadius/15);
                camera.poi[1] = spaceObject.getTransform()[13]+center[1]/2-(spaceObjectRadius/15);
                camera.poi[2] = spaceObject.getTransform()[14]+center[2]/2;
                // Set the camera's minimum distance
                camera.minDistance = Math.sqrt(spaceObjectRadius) * 5;
                camera.maxDistance = spaceObjectRadius * 10;
                // Set the camera's distance
                camera.distance = spaceObjectRadius * distanceScaler;
            }

            function whenLoaded()
            {
                 autoFocus(ship, distanceScaler);
                 if (subsys.length == 5) {
                     var sof = null;
                     if (booster == 'amarr') {
                         ccpwgl.getSofData( function(obj){ sof = obj; createSofBoostersForNonSofShip(ship, sof.race.amarr); ship.setBoosterStrength(1.5);});
                     } else if (booster == 'caldari') {
                         ccpwgl.getSofData( function(obj){ sof = obj; createSofBoostersForNonSofShip(ship, sof.race.caldari); ship.setBoosterStrength(1.5);});
                     } else if (booster == 'gallente') {
                         ccpwgl.getSofData( function(obj){ sof = obj; createSofBoostersForNonSofShip(ship, sof.race.gallente); ship.setBoosterStrength(1.5);});
                     } else if (booster == 'minmatar') {
                         ccpwgl.getSofData( function(obj){ sof = obj; createSofBoostersForNonSofShip(ship, sof.race.minmatar); ship.setBoosterStrength(1.5);});
                     }
                 }
                 else if (hull.substring(0, 5) != "res:/" && ship.setBoosterStrength !== undefined) {
                     ship.setBoosterStrength(1.5);
                 }
                 if (hull == 'ade3_t3') {
                     ship.t3dMode = 2;
                     ship.internalT3dMode =2;
                 }
            }

                ccpwgl.onPreRender = function ()
                {
                    if (!preRendered && !areLoadsPending())
                    {
                        preRendered = true;
                    }
                    if (!animationsLoading() && !overlaysAdded)
                    {
                        whenLoaded();
                        overlaysAdded = true;
                        if (!isMobile) {
                            for (var i = 0; i < turrets.length; i++) {
                                ship.mountTurret(i+1, turrets[i]);
                            }
                        }
                        if (overlays.length != 0){
                            var append = '';
                            for (var i = 0; i < ship.wrappedObjects.length; i++) {
                                if (ship.wrappedObjects[i].animation.animations.length)
                                {
                                    append = '_skinned';
                                }
                            }
                            for (var i = 0; i < overlays.length; i++) {
                                ship.addOverlay("res:/dx9/model/effect/"+overlays[i]+append+".red");
                            }
                            setInterval(effectLoop, 14000);
                        }
                    }
                };

                ccpwgl.onPostRender = function ()
                {
                    if (!hasProjectLoaded && !areLoadsPending())
                    {
                        if (ship.getBoundingSphere() != 0) {
                            hasProjectLoaded = true;
                            ccpwgl.enableRendering(true);
                            ccpwgl.enableUpdate(true);
                            canvas.style.opacity="1";
                            if (turrets.length != 0) {
                                setTimeout(doFire, 8000);
                            }
                            if (siege == 1) {
                                setTimeout(changeSiege, 15000);
                            }
                            if (hull.indexOf('de3_t3') != -1) {
                                setInterval(t3modeChange, 20000);
                            }
                        }
                    }
                };
            ccpwgl.enablePostprocessing(true)
        }

window.onDomReady = initReady;
 
      function initReady(fn)
      {
      	if(document.addEventListener) {
          document.addEventListener("DOMContentLoaded", fn, false);
        }
      	else {
          document.onreadystatechange = function(){readyState(fn)}
        }
      }
 
      function readyState(func)
      {
      	if(document.readyState == "interactive" || document.readyState == "complete")
      	{
      		func();
      	}
      }

window.onDomReady(onDocumentLoad);
