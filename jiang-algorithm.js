/*-----------------------------  jiang-algorithm.js  -------------------------------
This file consists of functions used to determine an individual's position
based on RSSI readings from various beacons. This implementation utilizes weights
in order to prioritize the closest, most accurate readings.
An optimal value for K, number of beacons to be utilized has yet to be discovered.
(Perhaps all beacons with an RSSI better than around |70|? |80|? Depends on how many
usable beacons this gives us in various locations...)
An optimal algorithm for determining weights {w1, w2, ... , wk} for every k beacon
yet to be discovered. It will scale based on RSSI linearly, linearithmically,
etc. Current implementation is linearly scaling weights -- the weight for every value is determined
based on its contribution to the total sum of rssi values.
----------------------------------------------------------------------*/
/*jshint esversion: 6*/

// Calculates distance (in feet afaik) with -65 being RSSI@1meter
// and n=2 being a constant (both provided by beacons)
function rssi_to_dist(rssi, A=-65, n=2 ) {
  var exp = (A - rssi)/(10*n),
    dist = 3.28*Math.pow(10, exp);
  return dist;
}

// new stuff

// beacon[i][3] is the RSSI, sort array by this value
function findKBest(beacons, k=7)
{
  var KBestArray = new Array(beacons.length).fill(-1000);
  beacons.forEach(function(beacon){
      KBestArray = insert(KBestArray, beacon);
  });
  return KBestArray;
}

function insert(array, value){
  array.splice(sortedArrayIndex(array, value),0, value);
  return array;
}

// for quicksort -- binary search js implementation sort of thing
function sortedArrayIndex(array, value){
  var low = 0,
  high = array.length;
    while (low < high) {
        // bitwise unsigned rightshift 1 -- to divide by 2 but much faster
        var mid = (low + high) >>> 1;
        // array[mid][3] just grabs the 4th element of the beacon -- the rssi
        if (array[mid][3] < value[3]){
          low = mid + 1;
        }
        else{
          high = mid;
        }
    }
    return low;
}

function getWeights(BeaconArray){
  var cumulativeRSSI, temp;
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
    beacon[4] = (InverseRSSI[BeaconArray.indexOf(beacon)]/cumulativeRSSI);
  });
  return BeaconArray;
}

function getAveragedLocation(BeaconArray){
  var x, y;
  BeaconArray.forEach(function(beacon){
    x += beacon[0] * beacon[5];
    y += beacon[1] * beacon[5];
  });
  var userLocation = [x, y];
  return userLocation;
}

function getWeightedPosition(beacons){
  // import data
  var x, y, r, d, i, w;
  var BeaconArray = new Array(beacons.length);
  i = 0;
  // stores x, y, rssi, distance (radius in feet), and placeholder weight
  while(i < beacons.length){
    x = beacons[i][0];
    y = beacons[i][1];
    // h = beacons[i][2];
    r = beacons[i][3];
    d = rssi_to_dist(beacons[i][3], beacons[i][4], beacons[i][5]);
    // will become the weight
    w = 1;
    BeaconArray[i] = [x, y, r, d, w];
    i++;
  }
  // sort and find top k beacons (k = 7 default)
  BeaconArray = findKBest(BeaconArray);
  // populate the w element in the BeaconArray array with weights (LINEARLY)
  BeaconArray = getWeights(BeaconArray);
  return["Error", BeaconArray.toString()];
  // average x, y values
  var userLocation = getAveragedLocation(BeaconArray);
  return [userLocation[0].toFixed(4), userLocation[1].toFixed(4)];

}
