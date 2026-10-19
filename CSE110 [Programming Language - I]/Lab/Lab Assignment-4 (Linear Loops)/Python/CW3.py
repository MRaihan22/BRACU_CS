while True:
    num = int(input("Enter number: "))
    if num < 0:
        print("Program Stopped!")
        break
    print(num, "^ 2 =", num*num)