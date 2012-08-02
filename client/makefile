INCLUDE = -I/usr/include/opencv2/ -I/usr/include/boinc/ -I/home/robert/BOINC_SOURCE/ -I/home/robert/BOINC_SOURCE/lib/ -I/home/robert/BOINC_SOURCE/api/
CXX = g++
LIBS = -lopencv_core -lopencv_highgui /home/robert/BOINC_SOURCE/api/libboinc_api.a /home/robert/BOINC_SOURCE/lib/libboinc.a 

wildlife: wildlife.o
	g++ $(INCLUDE) $^ -o $@ $(LIBS)
%.o: %.cpp
	$(CXX) $(INCLUDE) $< -c -o $@

.PHONY: clean

clean: 
	rm -rf wildlife read_avi2.o