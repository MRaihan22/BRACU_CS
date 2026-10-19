n = int(input("N = "))
sum = 0
print("Input the", n, "numbers:")
for i in range (0, n):
    nums = int(input())
    sum += nums
    avg = sum / n

print("The sum of", n, "no is:", sum)
print("The Average is:", avg)