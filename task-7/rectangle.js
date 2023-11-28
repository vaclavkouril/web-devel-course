function Sq(a,b) {
    const perimeter = 2*a+2*b;
    const area = a*b;
    
    console.log("Perimeter: " + perimeter);
    
    console.log("Area: " + area);
    

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

Sq(5,3);
