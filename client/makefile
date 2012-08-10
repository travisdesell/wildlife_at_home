INCLUDE = -I/usr/local/include -I/usr/include/opencv2/ -I/home/robert/BOINC_SOURCE/ -I/home/robert/BOINC_SOURCE/api/ -I/home/robert/BOINC_SOURCE/lib/
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

