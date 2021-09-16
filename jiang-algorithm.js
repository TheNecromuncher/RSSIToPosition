/*-----------------------------  jiang-algorithm.js  -------------------------------
This file consists of functions used to trilaterate an individual's position
based on RSSI readings from various beacons. This implementation utilizes weights
in order to prioritize the closest, most accurate readings.
An optimal value for K, number of beacons to be utilized has yet to be discovered.
(Perhaps all beacons with an RSSI better than around |70|? |80|? Depends on how many
usable beacons this gives us in various locations...)
An optimal algorithm for determining weights {w1, w2, ... , wk} for every k beacon
yet to be discovered. It will scale based on RSSI linearly, linearithmically,
etc. My idea is to instead add 100 to each RSSI value, add up the RSSI values and
assign a weight proportional to each based on its composition of the sum (linear)
----------------------------------------------------------------------*/

/*
Import previous ips implementation to borrow functions related to trilateration,
rssi value handling, etc.
*/
import * as ipsModule from 'ips.js';

// beacon[i][3] is the RSSI, sort array by this value
function findKBest(beacons, k=7)
{
//all obtained rssi values for a given 1000ms period as input_rssi
var a [beacons.length];
var sortedArr [k];
var dist;
//get all rssi values
index=0;
for (i in beacons)
{
a[index]=beacons[i][2]
index++;
}
//sorts the array of all the rssi values
a.sort(function(a, b) {
  return b - a;
})
// compares the top 7 in sorted rssi list, a, and the rssi values in the original beacon array.
// If a value matches, put the x, y, and rssi into a new array called sortedArr.
index=0;
for (var i=0; var<k; var++){
  for i in beacons
  {
  if a[1]==beacons[i][2]
    {
    sortedArr=beacons[i];
    index++;
    if index==k
    break;
    }
  }
  }
  return sortedArr;
}

function getWeights(BeaconArray){
  var cumulativeRSSI;
  var temp;
  var InverseRSSI = new Array(beacons.length);
  // fill InverseRSSI array with (100 - RSSI) from each beacon
  // accumulate the new sum of RSSIs to get (linearly) weighted average
  BeaconArray.forEach(function(beacon){
    temp = 100 - Math.abs(beacon[2]);
    InverseRSSI.push(temp);
    cumulativeRSSI += temp;
  });
  // each beacon gets an additional field, its weight, which is:
  // ( (100-RSSI) / new sum of RSSIs )
  BeaconArray.forEach(function(beacon){
    beacon[4] = (InverseRSSI[BeaconArray.indexOf(beacon)]/cumulativeRSSI)
  });
  return BeaconArray;
}

function getAveragedLocation(BeaconArray){
  var x, y;
  BeaconArray.forEach(function(beacon){
    x += beacon[0] * beacon[5];
    y += beacon[1] * beacon[5];
  })
  var userLocation = [x, y];
  return userLocation;
}

function getWeightedPosition(beacons){
  // import data
  var x, y, r, d, i;
  if(beacons.length < 3) {
    return ["ERROR", "Not enough beacons!"]
  }
  var BeaconArray = new Array(beacons.length);
  i = 0;
  // stores x, y, rssi, distance (radius in feet?), and placeholder weight
  while(i < num){
    x = beacons[i][0];
    y = beacons[i][1];
    // h = beacons[i][2];
    r = beacons[i][3];
    d = rssi_to_dist(beacons[i][3], beacons[i][4], beacons[i][5])
    // will become the weight
    w = 1
    BeaconArray[i] = [x, y, r, d, w];
    i++;
  }
  // sort and find top k beacons (k = 7 default)
  BeaconArray = findKBest(BeaconArray);
  // populate the w element in the BeaconArray array with weights (LINEARLY)
  BeaconArray = getWeights(BeaconArray);
  // average x, y values
  var userLocation = getAveragedLocation(BeaconArray);

  return userLocation;

}

