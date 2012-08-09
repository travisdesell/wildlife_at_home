INCLUDE = -I/opt/local/include -I/opt/local/include/opencv2/ -I/Users/deselt/Software/boinc/ -I/Users/deselt/Software/boinc/api/ -I/Users/deselt/Software/boinc/lib/
CXX = g++
LIBS = -lopencv_core -lopencv_highgui /home/robert/BOINC_SOURCE/api/libboinc_api.a /home/robert/BOINC_SOURCE/lib/libboinc.a 
FLAGS = -D_BOINC_ -Wall

wildlife: wildlife.o
	g++ $(INCLUDE) $(FLAGS) $^ -o $@ $(LIBS)
%.o: %.cpp
	$(CXX) $(FLAGS) $(INCLUDE) $< -c -o $@

.PHONY: clean

clean: 
	rm -rf wildlife wildlife.o

