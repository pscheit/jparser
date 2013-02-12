/**
 * Test JavaScript source containing various language constructs
 */
 
// Function Declaration
function MyFunc( arg1, arg2 ){
	// some primitives
	"\"Hello\"", true, false, null, undefined;
	// some number types
	0.123E+10, 100, 0xFFCC00, 0755;
	return this;
}

// Function Expression and variable declaration
var AnotherFunc = function(){
	return MyFunc;
};

// complicated call expression
var MyValue = AnotherFunc.apply( this, [ AnotherFunc, MyFunc ] );

// silly Unicode strings
var 今 = "\u4ECA";
var \u65E5 = "日";

// iteration statements
MyLoop : do {
	while( false ){
		for( var i = i, j = 0; i < 10; i++, j++ ){
			break MyLoop;
		}
		continue;
	}
}
while( false );

// conditional statements
if( true ){
	1 ? 2 : 3;
}
else if( false ){
	void 0;
}
else {
	null;
}

alert('Hello World');

// Did you know you can escape line breaks in JavaScript?
var str = 'Well,\
You can!';


