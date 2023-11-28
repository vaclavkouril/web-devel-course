// Implement functions manipulating a linked list
// An item in the linked list must be represented as an object
// with properties 'value' and 'rest' (see tests below)
// Example: {value:1, rest: {value:2, rest:null}}
// Empty list is 'null'.

/**
* Insert value at the beginning of the list
* (and return newly created list).
*/
function prepend(value, list) {
    return {value, rest: list };
}

/**
* Convert an array into a list (empty list is null).
*/
function arrayToList(array) {
    let list = null;
    for (let index = array.length-1; index >= 0; index--) {
        list = prepend(array[index],list);
    }
    return list;
}

/**
* Get n-th value from given list. Return undefined
* if the item does not exist.
*/
function nth(list, n) {
    if(!list)return undefined;
    if(n === 0) return list.value;
    return (nth(list.rest,n-1));
}

/**
* Convert a list into an array.
*/
function listToArray(list) {
    let array = [];
    while(list !== null){
        array.push(list.value);
        list = list.rest; 
    }
    return array;
}

/**
* Call callback with each item in the list.
*/
function forEach(list, callback) {
    while (list) {
    callback(list.value);
    list = list.rest;
    }
}

// TESTS, DO NOT MODIFY !!!

function test(value, expected) {
  const valueStr = JSON.stringify(value);
  const expectedStr = JSON.stringify(expected);
  console.log(valueStr == expectedStr
      ? "Test OK"
      : "Test FAILED: expecting " + expectedStr + ", got " + valueStr);
}

const threeItemsList = {value:1, rest: {value:2, rest:{value: 3, rest: null}}};
test(prepend(1, {value: 2, rest: {value: 3, rest: null}}), threeItemsList);
test(arrayToList([1, 2, 3]), threeItemsList);
test(nth(threeItemsList, 1), 2);
test(listToArray(threeItemsList), [1, 2, 3]);

test(prepend(1, null), {value:1, rest:null});
test(listToArray(null), []);
test(nth(null, 1), undefined);

let sum = 0;
forEach(arrayToList([1,2,3,4]), function (item) { sum += item; });
test(sum, 10);

  
