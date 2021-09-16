import Math;
import * as Jiang from 'Jiang.js'

var b=beacons.length;
var i=0;
var BeaconArray = new Array(b);
for (i < b){
BeaconArray[i] = [getRandomArbitrary(0,100), getRandomArbitrary(0,100), getRandomArbitrary(-100,0), getRandomArbitrary(0,100), getRandomArbitrary(0,1)];
console.log(BeaconArray[i]);
i++;
}


console.log(Jiang.getWeightedPosition());



function getRandomArbitrary(min, max) {
  return Math.random() * (max - min) + min;
}
