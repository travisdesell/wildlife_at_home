INCLUDE = -I/opt/local/include -I/opt/local/include/opencv2/ -I/Users/deselt/Software/boinc/ -I/Users/deselt/Software/boinc/api/ -I/Users/deselt/Software/boinc/lib/
CXX = g++
LIBS = -L/opt/local/lib -L/Users/deselt/Software/boinc_old/mac_build/build/Deployment -lboinc_api -lboinc -lopencv_core -lopencv_highgui

wildlife: wildlife.o
	g++ $(INCLUDE) $^ -o $@ $(LIBS)
%.o: %.cpp
	$(CXX) $(INCLUDE) $< -c -o $@

.PHONY: clean

clean: 
	rm -rf wildlife read_avi2.o
