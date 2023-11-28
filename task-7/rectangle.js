function Perimeter(a,b) {return 2*a+2*b;}
function Area(a,b) {return a*b;}

function SpecialLine(numofcolumns) {
    let line = "";
    for (let i = 0; i < numofcolumns; i++) {
        if(i === 0 || i === numofcolumns-1){ line += "*";}
        else {line += "-";}
    }
   return line
}

function NormalLine(numofcolumns) {
    let line = "";
    for (let i = 0; i < numofcolumns; i++) {
        if(i === 0 || i === numofcolumns-1){ line += "|";}
        else {line += "-";}
    }
   return line
}

function Sq(a,b) {
    
    console.log("Perimeter: " + Perimeter(a,b));
    
    console.log("Area: " + Area(a,b));
    

    for (let i = 0; i < b; i++) {
       let line = "";
        if (i===0 || i === b-1) {    
            line += "*"
            for (let y = 1; y < a-1; y++) {
                line += "-"                
            }
            line += "*"
        }
        else {    
            line += "|"
            for (let y = 1; y < a-1; y++) {
                line += "-"                
            }
            line += "|"
        }
        console.log(line);
    }
}

function SquareWriteReworked(sideA, sideB) {
    console.log("Perimeter: " + Perimeter(sideA,sideB));
    
    console.log("Area: " + Area(sideA,sideB));
    

    for (let i = 0; i < sideB; i++) {
        let line = "";
        if (i===0 || i === sideB-1) {line = SpecialLine(sideA);}
        else {line = NormalLine(sideA);}
        console.log(line);
    }

}

Sq(5,3);
SquareWriteReworked(5,3);
